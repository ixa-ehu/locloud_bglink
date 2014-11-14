locloud_bglink
==============

Background link service for LoCloud

This repository contains the background link service module developed within
the LoCloud project. The module consists of a PHP script that implements a
REST service, calls Dbpedia Spotlight for the actual processing, and wraps
the answer into a suitable format.

The background link service uses DBpedia Spotlight as a backbone for
performing the linking. In principle, the service can be used in any
language, the only requirement being that a DBpedia spotlight instance for
this particular language is running.

Installation instructions
=========================

### 1. Install DBpedia Spotlight for the required language.

### 2. Make sure that the DBpedia instance is running.  

One way to achieve this is to copy the following into this location:

    /etc/rc.local

Please configure the proper setting (path_to_dbpedia, language, port)

````shell
#!/bin/sh
#

touch /var/lock/subsys/local

path_to_dbpedia_jar=/home/lcuser/bglink
language=en
port=2222

echo "STARTING BGLINK SERVICE"
su - lcuser -c '/usr/bin/java -jar -Xmx4g ${path_to_dbpedia_jar}/dbpedia-spotlight.jar ${path_to_dbpedia_jar}/${language} http://localhost:${port}/rest &> /dev/null &'
sleep 180

````

### 3. Implement the REST service

Apache (or any other HTTP server) has to be installed in the machine for the
REST service to work. Then, copy the bglink.php script to this location:

    /var/www/html/rest/bglink.php

and make sure that the ownership and permissions and properly set. For
instance, they should be something like the following:

````shell
$ ls -al /var/www/html/rest/bglink.php
-rwxr-xr-x 1 root root 2812 Jul 28 08:14 /var/www/html/rest/bglink.php
````

### 4. Service usage

The API of the service is available through the support centre of the
LoCloud project at this address:

    http://support.locloud.eu/Metadata%20enrichment%20API%20technical%20documentation
