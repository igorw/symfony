<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Component\HttpFoundation\Ipv6;

use Symfony\Component\HttpFoundation\Ipv6\Normalizer;

class NormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
    * @dataProvider normalizeProvider
    */
    public function testNormalize($address, $expected)
    {
        $normalizer = new Normalizer();
        $this->assertEquals($expected, $normalizer->normalize($address));
    }

    public function normalizeProvider()
    {
        return array(
            // From: A Recommendation for IPv6 Address Text Representation
            // http://tools.ietf.org/html/draft-ietf-6man-text-addr-representation-07

            // Section 4: A Recommendation for IPv6 Text Representation
            // Section 4.1: Handling Leading Zeros in a 16 Bit Field
            array('2001:0db8::0001', '2001:db8::1'),

            // Section 4.2: "::" Usage
            // Section 4.2.1: Shorten As Much As Possible
            array('2001:db8::0:1', '2001:db8::1'),

            // Section 4.2.2: Handling One 16 Bit 0 Field
            array('2001:db8::1:1:1:1:1', '2001:db8:0:1:1:1:1:1'),

            // Section 4.2.3: Choice in Placement of "::"
            array('2001:db8:0:0:1:0:0:1', '2001:db8::1:0:0:1'),

            // Section 4.3: Lower Case
            array('2001:DB8::1', '2001:db8::1'),

            // Section 5: Text Representation of Special Addresses
            // We want to show IPv4-mapped addresses as plain IPv4 addresses, though.
            array('::ffff:192.168.0.1',            '192.168.0.1'),
            array('0000::0000:ffff:c000:0280',    '192.0.2.128'),

            // IPv6 addresses with the last 32-bit written in dotted-quad notation
            // should be converted to hex-only IPv6 addresses.
            array('2001:db8::192.0.2.128', '2001:db8::c000:280'),

            // Any string not passing the IPv4 or IPv6 regular expression
            // is supposed to result in false being returned.
            // Valid and invalid IP addresses are tested in
            // tests/regex/ipv4.php and tests/regex/ipv6.php.
            array('', false),
            array('192.168.1.256', false),
            array('::ffff:192.168.255.256', false),
            array('::1111:2222:3333:4444:5555:6666::', false),
        );
    }

    /**
     * @dataProvider inetNtopAndPtonProvider
     */
    public function testInetNtop($address, $hex)
    {
        $normalizer = new Normalizer();
        $this->assertEquals($address, $normalizer->inetNtop(pack('H*', $hex)));
    }

    /**
     * @dataProvider inetNtopAndPtonProvider
     */
    public function testInetPton($address, $hex)
    {
        $normalizer = new Normalizer();
        $this->assertEquals($hex, bin2hex($normalizer->inetPton($address)));
    }

    public function inetNtopAndPtonProvider()
    {
        return array(
            array('127.0.0.1',                        '7f000001'),
            array('192.232.131.223',                'c0e883df'),
            array('13.1.68.3',                        '0d014403'),
            array('129.144.52.38',                    '81903426'),

            array('2001:280:0:10::5',                '20010280000000100000000000000005'),
            array('fe80::200:4cff:fefe:172f',        'fe8000000000000002004cfffefe172f'),

            array('::',                                '00000000000000000000000000000000'),
            array('::1',                            '00000000000000000000000000000001'),
            array('1::',                            '00010000000000000000000000000000'),

            array('1:1:0:0:1::',                    '00010001000000000001000000000000'),

            array('0:2:3:4:5:6:7:8',                '00000002000300040005000600070008'),
            array('1:2:0:4:5:6:7:8',                '00010002000000040005000600070008'),
            array('1:2:3:4:5:6:7:0',                '00010002000300040005000600070000'),

            array('2001:0:0:1::1',                    '20010000000000010000000000000001'),
        );
    }
}
