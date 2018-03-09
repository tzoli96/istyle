#!/bin/bash

echo " * SERVICE VALIDATION ... "
if curl -I -H 'Host: istyle.eu' -H 'X-Forwarded-Proto: https' http://localhost/mk/ 2>&1 /dev/null | grep -q "HTTP/1.1 200 OK"; then
  echo -n "OK"
  exit 0
else
  echo -n "FAIL"
  exit 2
fi

