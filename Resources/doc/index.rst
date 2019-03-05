
Getting Started With VtereshenkovReservationBundle
==================================================

This bundle provides a way for create reservation for room / bed.  Is he add pages to  Sonata Admin Bundle, including graphic calendar.

Prerequisites
-------------

This version of the bundle requires Symfony 4.0+ and 
sonata-project/admin-bundle 3.35+, sonata-project/doctrine-orm-admin-bundle 3.6+.


Installation
------------

Installation process:

1. Download VtereshenkovReservationBundle using composer
2. Enable the Bundle
3. Configure the VtereshenkovSonataOperationBundle
4. Update your database schema
5. Execute sql file from Resources/doc/init.sql


Step 1: Download VtereshenkovReservationBundle using composer
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Add bundle repository in you composer.json

    "repositories": [
        {
            "type": "vcs",

            "url": "https://github.com/vtereshenkov/reservation-bundle"

        }

    ]

Require the bundle with composer:

.. code-block:: bash

    $ composer require vtereshenkov/reservation-bundle

Composer will install the bundle to your project's ``vendor/vtereshenkov/reservation-bundle`` directory.


Step 2: Enable the bundle
~~~~~~~~~~~~~~~~~~~~~~~~~

.. note::

    If you're using Flex, this is done automatically

Enable the bundle in the kernel:

    <?php
    // app/AppKernel.php

    public function registerBundles()
    {

        $bundles = array(
            // ...
            new Vtereshenkov\ReservationBundle\ReservationBundle(),
            // ...

        );

    }


Step 3: Configure the VtereshenkovReservationBundle
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. note::

    If you're using Flex, this is done automatically

Add the following configuration to your ``config/packages/vtereshenkov_reservation.yaml`` file.

    .. code-block:: yaml

        # config/packages/vtereshenkov_reservation.yaml
        vtereshenkov_reservation:
            user_provider: \App\Application\Sonata\UserBundle\Entity\User # Your ``User`` class which the implements Symfony\Component\Security\Core\User\UserInterface
            
            


Step 4: Update your database schema
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Now that the bundle is configured, the last thing you need to do is update your
database schema.

Run the following command.

.. code-block:: bash

    $ php bin/console doctrine:schema:update --force

Step 5: Execute sql file
========================

Import data from sql file Resources/doc/init.sql. this file containts data for payment status, resident status and order / invoice / reservation status.