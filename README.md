# Magento2 Jeftinije.hr, Ceneje.si, Idealno.rs and Idealno.ba

XML generator for Jeftinije.hr, Ceneje.si, Idealno.rs and Idealno.ba according to https://www.jeftinije.hr/xml-specifikacije


## Usage

1. Download jeftinije.php
2. Upload jeftinije.hr to Magneto 2 root folder (e.g. public_html)
3. Adjust config at the beginning of jeftinije.php script

That's it, your XML feed will be available at yourdomain.com/jeftinije.php


## Notes

* This script is not written as Mage module and it uses ObjectManager
* Tested only with simple products


## Future plans

1. Add support for attributes
2. Add support for various base categories
3. Save XML file to disk and generate file once per day using cron
4. Rewrite as Magento module