# MikroTik WG Auto

TL;DR: this tool lets you autoconfigure WireGuard clients on a MikroTik RouterOS and generate configs for them without 
hand-assigning any parameters.

## Why?
WireGuard is a static and **simple** [by design](https://www.wireguard.com). Thus, it does not offer any form of:

- automatic IP assignment
- route pushing
- config generation
- DHCP tunneling (or any non-IP traffic)

This is why normally to get a new node/person connection you have to:

- generate keys for the user (or ask the user for its public key)
- add new client
- find the next free IP & assign it statically a client
- create a config for the user
- make a note of which peer is which user

This tool does all that automatically for one or more users at once.

## Requirements
- RouterOS v7.1 [beta3 or newer](https://github.com/kiler129/mikrotik-auto-wireguard/issues/2)
- Admin user on the router with API enabled  
- PHP 7.4 or newer

## How to use it?
As of now, as the ROS is in beta stage, there are **no promises** of compatibility. In simple terms you should execute
`bin/console generate --help` and configure it as you wish ;)

For more see [more detailed docs](docs/README.md).

## Disclaimer
This is a beta software. As with ROSv7 it's not recommended being used in production. This software nor the author are
affiliated/supported/endorsed by [SIA Mikrotīkls](https://mikrotik.com/aboutus).
