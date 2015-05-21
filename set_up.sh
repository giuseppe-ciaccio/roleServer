/bin/rm -f ../ruoloispettori
ln -s $PWD/public ../ruoloispettori
chown apache:apache certs/roleserver.key
chmod 400 certs/roleserver.key
