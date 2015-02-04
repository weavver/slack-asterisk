# slack-asterisk
Description: A PHP script for integrating Slack with Asterisk  
Repository: https://github.com/weavver/slack-asterisk

Requirements:  
1. PHP: Tested against PHP 5.1.6  
2. Asterisk: Tested against Asterisk 1.4.29  

Copy this script or check out this git repo to your Asterisk server under a folder such as /usr/src/slack-asterisk

Usage: php notify.php channelname event arg1 arg2 etc
```
     Channel names should not include the #  
     Valid events:  
          incomingcall callerName callerNumber  
          outgoingcall callerName callerNumber destination  
          callanswered callerName callerNumber asteriskChannelId  
           |_ the asteriskChannelId is used to look up the extension the call was connected to  
           |_ we use this technique incase the dial command rings many phones  
          callhungup callerName callerNumber  
```

You can integrate this script into your dial plan using the following commands/techniques:  
  
1. Add an alert for incoming calls:
```
     System(php /usr/src/slack-asterisk/notify.php frontdesk-private incomingcall "${CALLERID(name)}" "${CALLERID(num)}");
```

2. Sending an outgoing call alert
```
     exten => s, 1, System(php /usr/src/slack-asterisk/notify.php frontdesk-private outgoingcall "${CALLERID(name)}" "${CALLERID(num)}" "${MACRO_EXTEN}");
```

3. Sending a call answered alert  
  
This action takes two parts, one is you have to hook into your Dial commands using the M() option, for example:  
```
     Dial(SIP/example,24,tM(callanswered^${CALLERID(name)}^${CALLERID(num)}^${CHANNEL}));
```

And the second part is to add a macro to your dialplan for Asterisk to trigger when a call is answered:
```
     [macro-callanswered]  
          exten => s, 1, System(php /usr/src/slack-asterisk/notify.php frontdesk-private callanswered "${ARG1}" "${ARG2}" "${ARG3}");
```

The macro will launch the notify.php script which will ping Slack with the answered message.

4. Sending a hang up alert
```
     exten => h, 1, System(php /usr/src/slack-asterisk/notify.php frontdesk-private callhungup "${CALLERID(name)}" "${CALLERID(num)}");
```


Notes: This script was designed to be really flexible so it should work with whatever dialplan scheme you have. Also it's fairly simple at under 200 lines that if you know some basic PHP you should be able to easily add to it/build from here.  
  
  
p.s. If you enjoy this please drop me a line at mythicalbox@weavver.com and let me know how you're using it, it's nice to hear how my code is doing in the wild!