/*
EthernetXbeeGW - Transparent with NTP and logging to MySQL
 
 v01  Øyvind Aardal
 */

#include <SPI.h>
#include <Ethernet.h>
#include <EthernetUdp.h>
#include <SoftwareSerial.h>
#include <Time.h>
#include <TimeAlarms.h>

byte mac[] = { 
  0xDE, 0xAD, 0xDE, 0xEE, 0xEE, 0xED };
IPAddress ip(192,168,10, 222);
//IPAddress gateway(192,168,10, 1);
//IPAddress subnet(255, 255, 255, 0);
IPAddress ntpserver(192, 43, 244, 18);
IPAddress sqlserver(192, 168, 10, 10);
const int NTP_PACKET_SIZE = 48;
const int LINEBUFFER_SIZE = 180;
unsigned int localUdpPort = 8888;
byte buffer[NTP_PACKET_SIZE];

//EthernetServer server(80);
EthernetClient SQLserver;
SoftwareSerial xbee(8,9);
EthernetUDP udp;

void setup() {
  Serial.begin(9600);
  Serial.println("XBEE-ETH-GW?version=1.0");
  Ethernet.begin(mac, ip);
//  server.begin();

  // Set up xbee on softwareserial 
  xbee.begin(9600);
  xbee.flush();      // clear noise

  // set up udp and ask for time 
  udp.begin(localUdpPort);
  setSyncInterval(60*10); // must be before setSyncProvider (why?)
  setSyncProvider(getNtpTime);

  // set up some repeating actions (timed)
  Alarm.timerRepeat(30, broadcastUtcTime);
}

char c;
byte p = 0;
boolean eol = true;  // means wait for cr then start to read a complete line
char line[LINEBUFFER_SIZE];      // buffer for string
String cmd;

void loop() {
  if (xbee.available()>0) { // catch xbee traffic and dump to serial
    c = xbee.read();

    if(c == 10) { // lf detected - read until next cr
      p = 0;
      eol = false;
    } 
    else if(c == 13) { // cr detected - go for it :-) - the p string is ready for parsing
      eol = true;
      line[p] = 0;

      if (line[0]=='G' && line[1]=='E' && line[2]=='T' & line[3]==' ') { // already formatted as it should - pass through to web server
        cmd = String(line);
      } 
      else { // not "GET " in first 4 bytes - parse as if it is the old meter module sending data
        // temporarily - write old style, ignore everything until 1st ,
        for(p=0;p<LINEBUFFER_SIZE;p++){
          if(line[p] == ','){
            p++;
            break;
          };
        }; // p is right after first ,

        cmd = "?count="; 
        while(line[p]!=',' && p < 80){
          cmd = cmd + line[p];
          p++;
        };
        p++; // now, were just behind the next the next , unless p went beyond 80

        cmd = cmd + "&kwhh=";

        while(line[p]!=',' && p < 80) {
          p++; // just skip the next parameter
        };
        p++; // now, were just behind the last ,

        while(line[p]!=0 && p < 80){ // add up until end of line
          cmd = cmd + line[p]; // 
          p++;
        };
        cmd = "GET /sql/" + cmd + " HTTP/1.0";
        Serial.println(cmd);
        // done with the temp stuff - should just write the command to the server
      }

      if(SQLserver.connect(sqlserver,80)){  // connect to SQL server 
        SQLserver.println(cmd);             // send command to SQL server
        SQLserver.println();                // request completed
        SQLserver.stop(); 
      } 
      else {
        Serial.println("Connection to SQL server failed");
      };

      p = 0;
    } 
    else { // it's not neither cr nor lf so continue
      line[p] = c;
      p++;
//      server.write(c);
//      Serial.write(c);
    };
  };

  /* Try to get by without acting as server
  EthernetClient client = server.available();

  if(client.connected()){ // if a client is connected
    if(client.available()>0) { // take the input from the client and spread the joy
      c = client.read();
      xbee.write(c);
      Serial.write(c);
    };    
  };
  */
  
  Alarm.delay(0); // invoke timers
}

// send an NTP request to the time server at the given address
unsigned long sendNTPpacket(IPAddress& address)
{
  // set all bytes in the buffer to 0
  memset(buffer, 0, NTP_PACKET_SIZE);
  // Initialize values needed to form NTP request
  // (see URL above for details on the packets)
  buffer[0] = 0b11100011;   // LI, Version, Mode
  buffer[1] = 0;     // Stratum, or type of clock
  buffer[2] = 6;     // Polling Interval
  buffer[3] = 0xEC;  // Peer Clock Precision
  // 8 bytes of zero for Root Delay & Root Dispersion
  buffer[12]  = 49;
  buffer[13]  = 0x4E;
  buffer[14]  = 49;
  buffer[15]  = 52;

  // all NTP fields have been given values, now
  // you can send a packet requesting a timestamp:         
  udp.beginPacket(address, 123); //NTP requests are to port 123
  udp.write(buffer,NTP_PACKET_SIZE);
  udp.endPacket();
}

unsigned long getNtpTime() {
  Serial.println("Request time using NTP");
  sendNTPpacket(ntpserver);

  int tries=50;
  Serial.print("Waiting");
  while(!udp.available() && tries > 0) {
    delay(100);
    Serial.print(".");
    tries--;
    if ((tries % 10) == 0) {
      sendNTPpacket(ntpserver); // try to send the NTP packet again, every 10th try, = second
      Serial.print("!");
    };
  };

  if(tries>0 && udp.parsePacket()){
    Serial.println("Response received");
    udp.read(buffer,NTP_PACKET_SIZE);
    unsigned long highWord = word(buffer[40], buffer[41]);
    unsigned long lowWord = word(buffer[42], buffer[43]); 
    unsigned long secsSince1900 = highWord << 16 | lowWord;  
    Serial.print("Seconds since Jan 1 1900 = " );
    Serial.println(secsSince1900);               
    Serial.print("Unix time = ");
    const unsigned long seventyYears = 2208988800UL;     
    unsigned long epoch = secsSince1900 - seventyYears;  
    Serial.println(epoch);                               
    Serial.print("The UTC time is ");       // UTC is the time at Greenwich Meridian (GMT)
    Serial.print((epoch  % 86400L) / 3600); // print the hour (86400 equals secs per day)
    Serial.print(':');  
    if ( ((epoch % 3600) / 60) < 10 ) {
      // In the first 10 minutes of each hour, we'll want a leading '0'
      Serial.print('0');
    };

    Serial.print((epoch  % 3600) / 60); // print the minute (3600 equals secs per minute)
    Serial.print(':'); 
    if ( (epoch % 60) < 10 ) {
      // In the first 10 seconds of each minute, we'll want a leading '0'
      Serial.print('0');
    };

    Serial.println(epoch %60); // print the second

    return secsSince1900 - seventyYears;// + adjustDstEurope(epoch);
  } 
  else {
    return 0; // means no new time discovered
  };

}

void broadcastUtcTime(){
  // digital clock display of the time
  String buff = "/utc/?time=";
  buff = buff + hour() + ":" + minute() + ":" + second() + "&date=" + 
    day() + "/" + month() + "/" + year();
  Serial.println(buff);
  xbee.println(buff);
//  server.println(buff);
  /*
  Serial.print(hour());
   printDigits(minute());
   printDigits(second());
   Serial.print(" ");
   Serial.print(day());
   Serial.print("/");
   Serial.print(month());
   Serial.print("/");
   Serial.print(year());
   Serial.println(); */
}

void printDigits(int digits){
  // utility function for digital clock display: prints preceding colon and leading 0
  Serial.print(":");
  if(digits < 10)
    Serial.print('0');
  Serial.print(digits);
}



