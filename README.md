# USSD Framework in PHP [![Travis Build Status](https://travis-ci.org/McAngelo/php-ussd-framework.svg?branch=master)]

Framework for building Ussd applications in PHP against the [Hubtel USSD API](http://developers.smsgh.com/documentations/ussd).

## Example Database Table Creation Statement
```sql
CREATE TABLE UssdSessions (
  UssdSessionId INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  SessionId VARCHAR(36) NOT NULL,
  Sequence INT NOT NULL,
  ClientState TEXT NOT NULL,
  DateCreated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```
