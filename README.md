Raspberry Pi 3 Magnetic Switch + relay controller
===========

This pure PHP implementation will read out a magnetic switch and depending on the input, it will control a relay,
allowing for a light to be turned on or off.

The idea is that the light will turn on ASAP when the door is opened, and will turn the lights off with a configurable
timer when the door has been closed.

It will additionally inform a MQTT broker of the sensors and commands that are being sent.

Used materials
--------

The materials used for this build are the following:

* [Magnetic switch](https://www.aliexpress.com/item/Free-Shipping-5-pcs-MC-38-MC38-Wired-Door-Window-Sensor-Magnetic-Switch-Home-Alarm-System/32255881055.html?spm=a2g0s.9042311.0.0.27424c4dj3ALXd)
* [Relay](https://www.aliexpress.com/item/Freeshipping-New-5V-2-Channel-Relay-Module-Shield-for-Arduino/1726504761.html?spm=a2g0s.9042311.0.0.27424c4dkd67Cr)
* Raspberry Pi 3b+ (Although any old rPi should be able to handle this program)

Schematics
--------

The general connections are made using the following diagram:
![Connections diagram](/magnetic-switch-kelder-schematics.png)

**Disclaimer**: Please ignore any errors in above drawing, I'm not an electrician. That being said, above diagram is
used to control devices dealing with AC voltage, if you don't even know what "AC" means, DO NOT use this guide and hire
somebody that knows about it!

Pin layout is based on this diagram:
![GPIO pin diagram](/rpi3-gpio-pins.png)

How to run the program
--------

This program consists of 2 scripts: one that primarily checks out what the status of the door is and opens up the relay,
while the other implements only a timer and will turn the relay down after the door has been closed.

**TODO: add commands**

Other information
--------

Check out [PHP/GPIO](https://github.com/PiPHP/GPIO), without that repo, this would be impossible in its current form.
