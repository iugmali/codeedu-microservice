<?php


namespace Tests\Feature\Models\Traits;



use Tests\Stubs\Models\UploadFilesStub;
use Tests\TestCase;

class UploadFilesTest extends TestCase
{
    private $obj;

    protected function setUp(): void
    {
        parent::setUp();
        $this->obj = new UploadFilesStub();
    }

    public function testMakeOldFilesOnSave()
    {
        UploadFilesStub::dropTable();
        UploadFilesStub::makeTable();
        $this->obj->fill([
           'name' => 'test',
           'file1' => 'test.mp4',
           'file2' => 'test2.mp4'
        ]);
        $this->obj->save();
        $this->assertCount(0, $this->obj->oldFiles);
        $this->obj->update([
           'name' => 'testupdated',
           'file2' => 'test3.mp4'
        ]);
        $this->assertEqualsCanonicalizing(['test2.mp4'],$this->obj->oldFiles);
    }

    public function testMakeOldFilesNullOnSaving()
    {
        UploadFilesStub::dropTable();
        UploadFilesStub::makeTable();
        $this->obj->fill([
            'name' => 'test'
        ]);
        $this->obj->save();
        $this->obj->update([
            'name' => 'testupdated',
            'file2' => 'test3.mp4'
        ]);
        $this->assertEmpty($this->obj->oldFiles);
    }

}
