## Examples

#### Get help
`bin/console generate --help`

...no, seriously, each option has help ;)

By default, the following options are assumed (and can be changed):
- Advanced/HTML template (with QR code readable in CLI and in a browser)
- Single user is generated
- Peer config routes all traffic via VPN (use `--allowed` to change)
- No pre-shared key (use `--psk` to enable)
- No keep-alive/[NAT helper](https://www.wireguard.com/quickstart/#nat-and-firewall-traversal-persistence)
- Use default (`wireguard1`) interface (use `--interface` to change)
- Next free IP is assigned from the WireGuard interface (use `--pool` if you want to use DHCP pool)
- External IP of the VPN server is the same as you use to call the API (use `--vpn-host` to change)
- External WireGuard port is read from the interface (use `--vpn-port` to change)

---

#### Minimal example
Generate a single user without name using default (`wireguard1`) interface:

`bin/console generate router.lan user password`

The generated config for the user will route all traffic through the VPN by default (use `--allowed` to change).

---

#### Multiple unnamed users
Generate 10 users without names:

`bin/console generate --num 10 router.lan user password`

---

#### Multiple named users
Generate 3 users (foo, bar, baz). The example shows both long (`--user`) and short (`-u`) invocation which are
interchangeable:

`bin/console generate --user foo -u bar -u baz router.lan user password`

Names of users will be saved in comments for each of the peers, and also included in printed output.

---

#### Multiple named users from a list file
Create a file `foo.txt` with:
```
foo
bar
baz
```

Then call the command below to generate 3 users (foo, bar, baz):

`bin/console generate --user-list foo.txt router.lan user password`

Names of users will be saved in comments for each of the peers, and also included in printed output.

---

#### Force PSK for users
Generate a single user without name using default (`wireguard1`) interface with pre-shared key enabled:

`bin/console generate --psk router.lan user password`

The generated config for the user will route all traffic through the VPN by default (use `--allowed` to change).

---

#### Change routes/allowed networks
Generate a single user with access two networks via split-tunnel VPN:

`bin/console generate --allowed 10.0.0.1/24 --allowed 10.100.0.1/24 router.lan user password`

---

#### Force a periodic refresh for road-warriors
Generate a single user with forced keep-alive every 60s:

`bin/console generate --keep-alive 60 router.lan user password`


This option is especially useful to [overcome NAT problems](https://www.wireguard.com/quickstart/#nat-and-firewall-traversal-persistence).

---

#### Use custom template
Generate a single user with a text-based template

`bin/console generate --teamplate resources/template/text.twig router.lan user password`

You can make your own templates too - [see docs](README.md#custom-templates).
