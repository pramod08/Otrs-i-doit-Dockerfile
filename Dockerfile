FROM ubuntu:14.04
MAINTAINER bizruntime


ENV DEBIAN_FRONTEND noninteractive

RUN apt-get -qq update && apt-get install -y wget nano apache2 supervisor libcrypt-ssleay-perl libencode-hanextra-perl libgd-gd2-perl \
 libgd-text-perl libgd-graph-perl libjson-xs-perl liblwp-useragent-determined-perl libmail-imapclient-perl libapache2-mod-perl2 \
 libnet-dns-perl libnet-ldap-perl libpdf-api2-perl libtext-csv-xs-perl libxml-parser-perl libyaml-perl libcrypt-eksblowfish-perl \
libyaml-libyaml-perl git  libnet-ldap-perl mysql-client-5.6 && apt-get install -y -q libapache2-mod-php5 php5 php5-cli php5-xmlrpc php5-ldap php5-gd php5-mysql mcrypt php5-mcrypt php5-curl  unzip wget  nano

# Supervisor
RUN mkdir -p /var/log/supervisor
ADD supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# OTRS
RUN wget http://ftp.otrs.org/pub/otrs/otrs-3.3.7.tar.bz2
RUN tar -C /opt -xjf otrs-3.3.7.tar.bz2 && rm otrs-3.3.7.tar.bz2 && mv /opt/otrs-3.3.7 /opt/otrs
RUN useradd -r -d /opt/otrs -c 'otrs' otrs
RUN usermod -G nogroup otrs
ADD Config.pm /opt/otrs/Kernel/Config.pm
RUN cd /opt/otrs/Kernel/Config && cp GenericAgent.pm.dist GenericAgent.pm
RUN cd /opt/otrs/var/cron && for foo in *.dist; do cp $foo `basename $foo .dist`; done
RUN cd /opt/otrs/bin && ./otrs.SetPermissions.pl /opt/otrs --otrs-user=otrs --otrs-group=nogroup --web-user=www-data --web-group=www-data
RUN ln -s /opt/otrs/scripts/apache2-httpd.include.conf /etc/apache2/conf-enabled/otrs.conf


RUN cd /opt/otrs && wget http://ftp.otrs.org/pub/otrs/itsm/bundle33/ITSM-3.3.7.opm && chown otrs:nogroup /opt/otrs/ITSM-3.3.7.opm \
 && su otrs -c "/opt/otrs/bin/otrs.PackageManager.pl -a install -p /opt/otrs/ITSM-3.3.7.opm" && \
rm /opt/otrs/ITSM-3.3.7.opm && cd /opt/otrs/bin && ./otrs.SetPermissions.pl /opt/otrs --otrs-user=otrs  --web-user=www-data --otrs-group=nogroup --web-group=www-data

# Set OTRS cron jobs
RUN su otrs -c "/opt/otrs/bin/Cron.sh start"

RUN apt-get clean && rm -rf /var/cache/apt/archives/* /var/lib/apt/lists/*

#i-doit installtion 
ADD i-doit.ini /etc/php5/mods-available/i-doit.ini
RUN sudo php5enmod i-doit && sudo php5enmod memcache
RUN php5enmod mcrypt
RUN mkdir /etc/php5/conf.d
RUN sudo ln -s /etc/php5/mods-available/i-doit.ini /etc/php5/conf.d/

RUN a2ensite 000-default.conf && a2enmod rewrite
RUN mkdir /var/www/i-doit

ADD i-doit /var/www/i-doit
RUN chmod +x /var/www/i-doit/idoit-rights.sh
RUN cd /var/www/i-doit && ./idoit-rights.sh

COPY 000-default.conf /etc/apache2/sites-enabled/000-default.conf
WORKDIR /var/www/i-doit
RUN  chown www-data:www-data -R . && find . -type d -name \* -exec chmod 775 {} \; && find . -type f -exec chmod 664 {} \; && chmod 774 controller tenants import updatecheck *.sh


EXPOSE 80  

CMD ["/usr/bin/supervisord"]



