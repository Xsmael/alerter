var email 	= require("emailjs");
var server 	= email.server.connect({
   user:    "register@tices.org",
   password:"#Re-gister777!",
   host:    "dolphin.o2switch.net",
   port:    465,
   ssl:     true
});

// send the message and get a callback with an error or details of the message that was sent
server.send({
   text:    "i hope this works",
   from:    "TICE <register@tices.org>",
   to:      "puissance.ouedraogo@unicom-sa.com, puissancisma@gmail.com",
   subject: "testing emailjs with port 465 sssssL"
}, function(err, message) { console.log(err || message); });

