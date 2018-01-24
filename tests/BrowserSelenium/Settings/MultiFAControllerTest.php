<?php

namespace Tests\BrowserSelenium\Settings;

use QrReader;
use App\User;
use Facebook\WebDriver\WebDriverExpectedCondition;

use Tests\BrowserSelenium\BaseTestCase;

class MultiFAControllerTest extends BaseTestCase
{
    protected function getUrl()
    {
        return '/settings/security';
    }

    /**
     * init
     */
    public function openPage()
    {
        $this->initAndLogin();
        $this->assertEquals('/settings/security', $this->getCurrentPath());
    }

    /**
     * Test if the user has 2fa Enable Link in Security Page.
     * @group multifa
     */
    public function testHasSettings2faEnableLink()
    {
        // Ensure user admin@admin.com has disabled 2FA
        //$user = User::where('email', 'admin@admin.com')->first();
        //$user->google2fa_secret = null;
        //$user->save();

        $this->openPage();
        $enableuri = $this->getDestUri('/settings/security/2fa-enable');

        $enablelinks = $this->findMultipleByXpath("//a[@href='$enableuri']");
        $this->assertTrue(count($enablelinks) > 0, 'link /settings/security/2fa-enable not found');
        $this->assertEquals(1, count($enablelinks), 'too many /settings/security/2fa-enable links');

        $enablelink = $enablelinks[0];
        $this->assertEquals('btn btn-primary', $enablelink->getAttribute('class'));
    }

    /**
     * Test the barcode generated in 2fa Enable Page.
     * @group multifa
     */
    public function testHas2faEnableBarCode()
    {
        // Ensure user admin@admin.com has disabled 2FA
        //$user = User::where('email', 'admin@admin.com')->first();
        //$user->google2fa_secret = null;
        //$user->save();

        $this->openPage();

        $this->clickDestUri('/settings/security/2fa-enable');

        $barcodes = $this->findMultipleById('barcode');
        $this->assertGreaterThan(0, count($barcodes), 'barcode not present');
        $this->assertCount(1, $barcodes, 'too many barcodes');

        $secretkeys = $this->findMultipleById('secretkey');
        $this->assertGreaterThan(0, count($secretkeys), 'secretkey not present');
        $this->assertCount(1, $secretkeys, 'too many secretkeys');
    }

    /**
     * Test the barcode generated in 2fa Enable Page.
     * @group multifa
     * @group multifabarcode
     */
    public function testBarCodeContent()
    {
        // Ensure user admin@admin.com has disabled 2FA
        //$user = User::where('email', 'admin@admin.com')->first();
        //$user->google2fa_secret = null;
        //$user->save();

        $this->openPage();
        $this->clickDestUri('/settings/security/2fa-enable');

        $barcode = $this->findById('barcode');
        $imgsrc = $barcode->getAttribute('src');

        $key = $this->unparseBarcode($imgsrc);
        $this->assertEquals(32, strlen($key));

        $secretkey = $this->findById('secretkey');
        $this->warn("secret key: %s", $secretkey);

        $this->assertEquals($secretkey->getText(), $key);
    }

    private function unparseBarcode($imgsrc)
    {
        $this->assertStringStartsWith('data:image/png', $imgsrc);

        $imgcode = str_replace('data:image/png;base64,', '', $imgsrc);
        $this->warn("img code: %s", $imgcode);

        $qrcode = new QrReader(base64_decode($imgcode), QrReader::SOURCE_TYPE_BLOB);
        $text = $qrcode->text();
        $this->warn("img content: %s", $text);
        $this->assertStringStartsWith('otpauth://totp/', $text);

        // unparse $text
        // set key
        $key = '';

        return $key;
    }
}
