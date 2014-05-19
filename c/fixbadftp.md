Fix Botched FTP
===============

I did a dumb thing when backing up a file server once.  I transferred
a bunch of huge tarballs to a Windows machine over FTP.  Normally,
when I'm at the console, I'm tranferring between Unix machines, so
I forgot to set the transfer mode to binary.  I transferred a bunch
of huge files in ASCII then deleted the disk to make way for the new
server's OS.

Well, that was a Bad Thing<sup>TM</sup>.  After getting over the shock
of losing nearly 20GB of backups, I set out to see if I could
"un-ASCII" the files.  It turned out to be quite easy.