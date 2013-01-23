#!/bin/bash

#Author: Frco. Javier Rial Rodríguez
#email: fjrial@cesga.es, fjrial@gmail.com
#Copyright (C) 2013 - CESGA

#This script is meant to be configured as remote command in Zabbix.
#It queries the RT (Request Tracker) Database about a ticket opened
#with a custom subject.
#It a tickets exists, it does nothing
#If does not exist, it creates a new one


#This program is free software; you can redistribute it and/or modify
#it under the terms of the GNU General Public License as published by
#the Free Software Foundation; either version 2 of the License, or
#(at your option) any later version.

#This program is distributed in the hope that it will be useful,
#but WITHOUT ANY WARRANTY; without even the implied warranty of
#MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#GNU General Public License for more details.


#INPUT PARAMETERS FOR THIS SCRIPT
#Set accordingly in the "remote-command" in Zabbix
#You have to monitor your zabbix-server and allow the execution of remote commands in zabbix_agentd

#VMW-ZABBIX:/path_to_script/check_ticket.sh '{HOSTNAME}' {EVENT.DATE} {EVENT.TIME} {STATUS} '{TRIGGER.NAME}' {IPADDRESS} '{HOSTNAME}' {ITEM.LASTVALUE} DOWN

#$1 {HOSTNAME}
#$2 {EVENT.DATE
#$3 {EVENT.TIME} 
#$4 {STATUS} 
#$5 {TRIGGER.NAME} 
#$6 {IPADDRESS} 
#$7 {HOST.NAME} 
#$8 {ITEM.LASTVALUE} 


#FROM EMAIL, who send the email to RT
FROM_EMAIL="email@email.com"
#email associated with the target queue in RT
QUEUE_EMAIL="email_queue@email.com"

#---RT Mysql Settings---#
#D database name 
#P=password mysql
#U=user mysql
#H=server
D="db_name"
P="pass"
U="user"
H="host"

#Query RT to check if there is a ticket open for host..
#customize to match your needs"
QUERY_RT="use $D;select id from Tickets where queue=6 and Subject like '[NOC %], $1% DOWN %' and (Status='new' or Status='open')";

#Getting the result of the query, it should be an id if there is a ticket already opened
id=`mysql -h$H -u $U -p$P -ss -e "$QUERY_RT"`

#Check if Id is numeric
#--if it's numeric, it's the id of the ticket
#--if it's not, it's the query returning empty result -> do nothing

#send mail to ticket rt
if ! [[ "$id" =~ ^[0-9]+$ ]] ; then   
  cf1="CF.{custom_fields}: value"
  
  #CUSTOM FIELDS IN RT, uncomment and configure to suit your needs
  #cf2="CF.{Importancia}: Baja"
  #cf3="CF.{Tipo}: Incidencia"
  #cf4="CF.{Origen}: Interno"
  #cf5="CF.{Modo}: Automático"
  #cf6="CF.{Urgencia}: Bajo"
  #cf7="CF.{Ambito}: Red"
  #cf8="CF.{Impacto}: Bajo"

  #email body
  linea1="$5"
  linea2="Date             :    $2 - $3"
  linea3="IP                   :     $6"
  linea4="Host           :     $7"
  linea5="Last value  :     $8"

  echo "Subject: [NOC #$2-$3], $1 DOWN" > /tmp/File_Out$1
  echo "FROM: $FROM_EMAIL" >> /tmp/File_Out$1
  echo "To: $QUEUE_EMAIL" >> /tmp/File_Out$1
  echo "Content-Type: text/plain;charset=utf-8" >> /tmp/File_Out$1
  echo "" >> /tmp/File_Out$1
  echo "" >> /tmp/File_Out$1
  echo $cf1 >> /tmp/File_Out$1
  
  #CUSTOM FIELDS IN RT, uncomment and configure to suit your needs
  #echo $cf2 >> /tmp/File_Out$1
  #echo $cf3 >> /tmp/File_Out$1
  #echo $cf4 >> /tmp/File_Out$1
  #echo $cf5 >> /tmp/File_Out$1
  #echo $cf6 >> /tmp/File_Out$1
  #echo $cf7 >> /tmp/File_Out$1
  #echo $cf8 >> /tmp/File_Out$1
  echo "" >> /tmp/File_Out$1
  echo "" >> /tmp/File_Out$1
  echo $linea1 >> /tmp/File_Out$1
  echo $linea2 >> /tmp/File_Out$1
  echo $linea3 >> /tmp/File_Out$1
  echo $linea4 >> /tmp/File_Out$1
  echo $linea5 >> /tmp/File_Out$1

  sendmail "$QUEUE_EMAIL" < /tmp/File_Out$1

fi
