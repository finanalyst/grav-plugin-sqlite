---
title: Confirm
form:
  name: confirm-user
  fields:
    - name: confirmed
      label: "Data is correct"
      type: radio
      options:
        confirmed: Confirmed
        cancel: Cancel
      help: "Confirm that the data is correct"
  buttons:
    - type: submit
      value: Confirm
  process:
    - sqliteConfirm
---
In plugin
