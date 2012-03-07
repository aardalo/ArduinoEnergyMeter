
// Inspired by:
//////////////////////////////////////////////////////////////////
//©2011 bildr
//Released under the MIT License - Please reuse change and share
//Simple code for the TMP102, simply prints temperature via serial
//////////////////////////////////////////////////////////////////

/* Wiring
 **  Arduino - TMP102 - connected with CAT5 cable as follows:
 **    Brown->SCL, Brown/White->GND
 **    Blue->SDA, Blue/White->V+
 **    Orange->ADD0, Orange/White->ALT
 **    Green+Green/White to LDR
 */

#include <Wire.h>
#include <stdio.h>
int tmp102Address = 0x48;
char temp[38];                          // temp is actually temperature, not just a temporary variable

void setup(){
  Serial.begin(9600);
  Wire.begin();
}

unsigned long lastMillis = 0;

void loop(){
  String loggString;
  if(millis()-lastMillis>=30000) {        // Just check every 30 sec
    lastMillis = millis();                // first to avoid drift
    
    // Check the temperature
    float celsius = getTemperature();     // Read the I2C temp sensor
    dtostrf(celsius,0,2,temp);            // convert float to string, left orientation, 2 decimals
    loggString = "GET /sql/?table=temp&sensor=utetemp&value=" + String(temp) + " HTTP/1.0"; // assemble logg command to XBEE to ETH GW
    Serial.println(loggString);           // put it out on the XBEE network
    
    delay(1000);
    
    // Check the ambient light using the LDR
    int ldr = analogRead(A3);
    loggString = "GET /sql/?table=temp&sensor=lys&value=" + String(ldr) + " HTTP/1.0";
    Serial.println(loggString);
  }
}

float getTemperature(){ // Read temperature from TMP102 I2C device
  Wire.requestFrom(tmp102Address,2); 

  byte MSB = Wire.read();
  byte LSB = Wire.read();

  //it's a 12bit int, using two's compliment for negative
  int TemperatureSum = ((MSB << 8) | LSB) >> 4; 

  float celsius = TemperatureSum*0.0625;
  return celsius;
}

