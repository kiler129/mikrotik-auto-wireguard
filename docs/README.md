# MikroTik WG Auto

### Overview of options
A loose list of currently possible scenarios with the tool:

- generation of text-based configs in CLI
- generation of configs from templates (HTML or text, see `resources/template/` for examples)
- creating QR codes with configs (for mobile apps)  
- assigning IPs from DHCP pools (`/ip pool`) or from interfaces (`/ip address`)
- optionally autogenerating [pre-shared keys](https://www.wireguard.com/protocol/) for extra security
- 

### Examples
See [`EXAMPLES.md`](EXAMPLES.md).

### Custom templates
The tool allows for a custom template to be specified. Templates are using [Twig engine](https://twig.symfony.com/doc/3.x/).
To use a template set option `--teamplate` (`-t` for short). By default [`resources/template/advanced.twig`](../resources/template/advanced.twig) 
full-featured template is used. The code also includes a simpler, text-based template: [`resources/template/text.twig`](../resources/template/text.twig). 

