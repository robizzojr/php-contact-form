<?php

include __DIR__ . "/../contactHandler.php";

final class StackTest extends PHPUnit_Framework_TestCase
{
    //Test connection to database
    public function testCanConnectToDatabase(): void
    {
	$this->assertSame("mysqli", get_class(getConnection()));
    }

    //Test that script fails without name field being submitted
    public function testCantSendWithoutRequiredField(): void
    {
	global $statusMessage;
	$this->assertSame('Name cannot be empty', $statusMessage);
    }
}
