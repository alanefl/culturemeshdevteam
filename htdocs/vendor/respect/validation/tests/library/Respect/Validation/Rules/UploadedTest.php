<?php

namespace Respect\Validation\Rules;

$GLOBALS['is_uploaded_file'] = null;

function is_uploaded_file($uploaded)
{
    $return = \is_uploaded_file($uploaded); // Running the real function
    if (null !== $GLOBALS['is_uploaded_file']) {
        $return                         = $GLOBALS['is_uploaded_file'];
        $GLOBALS['is_uploaded_file']    = null;
    }

    return $return;
}

class UploadedTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @covers Respect\Validation\Rules\Uploaded::validate
     */
    public function testValidUploadedFileShouldReturnTrue()
    {
        $GLOBALS['is_uploaded_file'] = true;

        $rule = new Uploaded();
        $input = '/path/of/a/valid/uploaded/file.txt';
        $this->assertTrue($rule->validate($input));
    }

    /**
     * @covers Respect\Validation\Rules\Uploaded::validate
     */
    public function testInvalidUploadedFileShouldReturnFalse()
    {
        $GLOBALS['is_uploaded_file'] = false;

        $rule = new Uploaded();
        $input = '/path/of/an/invalid/uploaded/file.txt';
        $this->assertFalse($rule->validate($input));
    }

    /**
     * @covers Respect\Validation\Rules\Uploaded::validate
     */
    public function testShouldValidateObjects()
    {
        $GLOBALS['is_uploaded_file'] = true;

        $rule = new Uploaded();
        $object = new \SplFileInfo('/path/of/an/uploaded/file');

        $this->assertTrue($rule->validate($object));
    }

}
