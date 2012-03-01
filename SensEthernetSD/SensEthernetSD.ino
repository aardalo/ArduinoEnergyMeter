
/*
Sensor
    WITH SD card for local storage 
     AND ethernet for remote saving

TSL237 Light-to-voltage Optical Converter OUT to pin 3 and pulldown 10k
SD on pin 4

Hardware:
	Arduino Ethernet Board (includes SD)
	http://arduino.cc/en/Main/ArduinoBoardEthernet
	
	TSL237-LF
	http://www.taosinc.com/ProductDetails.aspx?id=73

Based on EthernetXbeeGW (v01) by Øyvind Aardal (ethernet)
  AND on int3drevettsl257medSD by Øyvind Aardal (sensor + SD)
 
 v01  Hallvard Nygård

TODO:
	DNS and DHCP
*/

#include <SPI.h>
#include <Client.h>
#include <Ethernet.h>
#include <SoftwareSerial.h>
#include <SD.h>

// Mac and IP of the Arduino
byte mac[] = { 0xDE, 0xAD, 0xDE, 0xEE, 0xEE, 0xED };
byte ip[] = { 192,168,0,222 };

// HTTP servers IP
byte server[] = { 192, 168, 0, 125 }; 

// Initialize the Ethernet client library
// with the IP address and port of the server 
// that you want to connect to (port 80 is default for HTTP):
Client client(server, 80);

volatile unsigned int int3C = 0;    // interrupt pin 3 used to count pulses
long lastT = 0;

#define chipSelect 4   // SD on pin 4 
#define intPin 1       // interrupt 1 is hardwired to pin 3

String cmd;

void setup() {
  Serial.begin(9600);
  Serial.println("SENS-ETH-SD?version=1.0");
  Ethernet.begin(mac, ip);
  

  char name[] = "APPEND.TXT";
  attachInterrupt(intPin, int3, RISING);
  pinMode(chipSelect ,OUTPUT); // Enable SD card

  if (!SD.begin(chipSelect)) {
    Serial.println("Card failed, or not present");
    return;
  }
  Serial.println("card initialized.");
}

void loop() {
  long nowT = millis();
  long diffT = nowT - lastT;

  if (diffT >= 5000) {
    unsigned int impulses = int3C;
    int3C = 0;  
    String loggString = "";
    
/* ADD * and / to end of this line to comment IN
    Serial.print(millis());
    Serial.print(":Impulses ");
    Serial.print(impulses);
    Serial.print(" DiffT ");
    Serial.print(diffT);
    Serial.print(" kWh/h ");
    Serial.print(1000.0*60*60/diffT*impulses/10000);
    Serial.println(".");
/* END OF COMMENT OUT DEBUG INFO */

    loggString = String(millis()) + "," + String(impulses) + "," + String(diffT);

    // :: Print to serial port
    Serial.print(loggString);
    Serial.print(",");
    Serial.println(1000.0*60*60/diffT*impulses/10000);

    // :: Write to SD card
    // open the file. note that only one file can be open at a time,
    // so you have to close this one before opening another.
    File dataFile = SD.open("datalog.txt", FILE_WRITE);

    // if the file is available, write to it:
    if (dataFile) {
      dataFile.print(loggString);
      dataFile.print(",");
      dataFile.println(1000.0*60*60/diffT*impulses/10000);
      dataFile.close();
    }  
    // if the file isn't open, pop up an error:
    else {
      Serial.println("error opening datalog.txt");
    }

    cmd = "GET /myfolder/?line=" + loggString + " HTTP/1.0";
    Serial.println(cmd);
    // Write the command to the server
    if(client.connect()){
      Serial.println("Connected to HTTP server");
      client.println(cmd);
      c
      
      lient.println(); // request completed
      client.stop();
    } else {
      Serial.println("Connection to HTTP server failed");
    };

    lastT = nowT;
  }
}

void int3(){
  int3C++;
}

