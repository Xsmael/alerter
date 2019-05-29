var fs= require("fs");
var figlet = require('figlet');
var path=  require('path');
const express = require('express');
var request = require('request');
const bodyParser = require('body-parser');
var log = require('noogger').init({ consoleOutputLevel: ['INFO', 'NOTICE', 'ERROR', 'WARNING', 'CRITICAL','DEBUG'],  fileOutputLevel: ['INFO', 'NOTICE', 'ERROR', 'WARNING', 'CRITICAL', 'DEBUG'] });

var WEBSOCKET = require('ws');
var WebSocketServer = WEBSOCKET.Server;
var CONF= {
    WS_PORT: 3003,
    ES_PORT: 3005,
    QDIR:'queues',
    SECRET: 'Async#70'
}

var wss = new WebSocketServer({
    port: CONF.WS_PORT
}, () => {
    log.info('WS Server started on PORT: ' + CONF.WS_PORT);

});

const app = express();
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));





function generateToken() { return Math.random().toString(36).substring(2, 15) + '-' + Math.random().toString(36).substring(2, 15) + '-' + Date.now().toString(36); }


app
.get('/signal', function (req, res) {

    log.debug('alert');
    log.debug(req.query);

    let alert= req.query;
    if(alert.queueId ) {
        let qID= alert.queueId;        
        let fileName= path.join(CONF.QDIR, qID+'.q');
        delete alert.queueId;

        enQueue(fileName, alert);
        res.json({ success: true });
    } else if (alert.channels) {

        res.json({ success: true });
    }
    else {
        log.warning("No queue id or channel is defined, ignored");
        res.json({ success: false, error:"No queue id or channel is defined, ignored" });
    }


})

.get('/describe', function (req, res) {

    log.debug('queue descriptor');
    log.debug(req.query);

    let qd= req.query;
  
    res.json({ success: true });

})


.get('/subscribe', function (req, res) {

    log.debug('queue descriptor');
    log.debug(req.query);

    let qd= req.query;
  
    res.json({ success: true });

})
;



function init() {   
    
    console.log();    
    console.log();    
    console.log(figlet.textSync('ALERTER', { font: 'Ghost', horizontalLayout: 'full'}));
    console.log(figlet.textSync('v 1.1.0', { font: 'Ghost',}));
    console.log();      

    log.info("Starting up Sequence...") ;

    app.listen(CONF.ES_PORT, () => {
        log.info('Express Server started on PORT: ' + CONF.ES_PORT);
    });
}
init() ;



function generateToken() { return Math.random().toString(36).substring(2, 15) + '-' + Math.random().toString(36).substring(2, 15) + '-' + Date.now().toString(36); }


function createQueueFile(fileName) {
    // let fileName= path.join(CONF.QDIR,qID+'.q');
    fs.exists(fileName, function (yes) {
        if(!yes) 
        log.notice(" initialising "+fileName);
            fs.writeFile(fileName,"[]", function (err) {
                if(err) log.warning("failed to create queue file at: " + fileName);
            });
    });
}

function enQueue(fileName, data) {

    fs.readFile(fileName, function (err, queueString) {
        if(err){
            log.error(err);
            if(err.code=='ENOENT') { createQueueFile(fileName); queueString="[]"; }// It is a new queue and the File has not yet been created....hence we create it
            else log.critical("Failed to open file: "+fileName + " "+err );
        } 
        
        if(!err || err.code=='ENOENT'){
            let queue= JSON.parse(queueString);
            queue.push(data);
            fs.writeFile(fileName, JSON.stringify(queue), function (err) {
                if(err) log.critical("Failed to write to file: "+fileName)
            })
        }

    })
}
