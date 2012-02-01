/*
**  Power-logger with SD and XBee by Øyvind Aardal
**  
**  TSL237 Light-to-voltage Optical Converter OUT to pin 3 and pulldown 10k
**  DFrobot SD-card on SPI, SS pin 10, MOSI pin 11, MISO pin 12, SCK pin 13
**  
*/


#include <SD.h>

volatile unsigned int int3C = 0;    // interrupt pin 3 used to count pulses
long lastT = 0;

#define chipSelect 10
#define intPin 1       // interrupt 1 is hardwired to pin 3

void setup () {
  char name[] = "APPEND.TXT";
  Serial.begin(9600);
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

    // open the file. note that only one file can be open at a time,
    // so you have to close this one before opening another.
    File dataFile = SD.open("datalog.txt", FILE_WRITE);

    // if the file is available, write to it:
    if (dataFile) {
      dataFile.print(loggString);
      dataFile.print(",");
      dataFile.println(1000.0*60*60/diffT*impulses/10000);
      dataFile.close();
      // print to the serial port too:
      Serial.print(loggString);
      Serial.print(",");
      Serial.println(1000.0*60*60/diffT*impulses/10000);
    }  
    // if the file isn't open, pop up an error:
    else {
      Serial.println("error opening datalog.txt");
    }

    lastT = nowT;
  }
}

void int3(){
  int3C++;
}



