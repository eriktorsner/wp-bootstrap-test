# wp-bootstrap-test
A separate repo for testing wp-bootstrap. Initiates a vagrant box with all
relevant tools installed. Run phpunit etc. from inside vagrant to run tests
against a proper WordPress installation.

## Getting started

    $ git clone git@github.com:eriktorsner/wp-bootstrap-test.git
    $ cd wp-bootstrap-test
    $ git clone git@github.com:eriktorsner/wp-bootstrap.git
    $ vagrant up
    $ vagrant ssh

    $ cd /vagrant
    $ phpunit





