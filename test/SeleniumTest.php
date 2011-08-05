<?php

/*
 * Base class for Envaya's Selenium tests in /test/testcases .
 */

require_once 'Selenium.php';

class SeleniumTest extends PHPUnit_Framework_TestCase
{
    protected $s;
    
    public function setUp()
    {
        echo "\n".get_class($this)."\n";
        $this->deleteMailFile();
        $this->startBrowser();
    }
    
    public function startBrowser()
    {
        $this->s = $this->init_selenium();
        $this->start();
        $this->windowMaximize();
    }
    
    public function deleteMailFile()
    {
        global $TEST_CONFIG;        
        @unlink($TEST_CONFIG['mock_mail_file']);    
    }
    
    public function init_selenium()
    {
        global $BROWSER, $TEST_CONFIG;
        return new Testing_Selenium("*$BROWSER", "http://{$TEST_CONFIG['domain']}");    
    }

    /*
     * Allows all functions on selenium object $this->s to be called directly on SeleniumTest object,
     * e.g. $this->click('//a');
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->s, $name), $arguments);
    }
    
    public function tearDown()
    {
        $this->s->stop();
    }

    function login($username, $password)
    {
        $this->waitForElement("//input[@name='username']");
        $this->type("//input[@name='username']",$username);
        $this->type("//input[@name='password']",$password);
        $this->submitForm();    
    }    
    
    function waitForElement($xpath, $timeout = 15)
    {
        $this->retry('mouseOver', array($xpath), $timeout);
    }    
    
    function ensureGoodMessage()
    {
        $this->mouseOver("//div[@class='good_messages']");
    }
    
    function ensureBadMessage()
    {
        $this->mouseOver("//div[@class='bad_messages']");
    }
    
    public function mustNotExist($id)
    {
        if ($this->isElementPresent($id))
        {
            throw new Exception("Element $id exists");
        }
    }   
    
    public function mustBeVisible($id)
    {
        if (!$this->isVisible($id))
        {
            throw new Exception("Element $id is not visible");
        }
    }       
    
    public function mustNotBeVisible($id)
    {
        if ($this->isVisible($id))
        {
            throw new Exception("Element $id is visible");
        }
    }           
        
    public function waitForPageToLoad($timeout = 10000)
    {
        $this->s->waitForPageToLoad($timeout);
    }

    public function submitForm($button = "//button[@type='submit']")
    {
        $this->clickAndWait($button);
    }

    public function clickAndWait($selector)
    {
        $this->click($selector);
        $this->waitForPageToLoad(10000);
    }
    
    public function isElementInPagedList($elem)
    {
        while (true)
        {
            if ($this->isElementPresent($elem))
            {
                return true;
            }
        
            if (!$this->isElementPresent("//a[@class='pagination_next']"))
            {
                break;
            }
            $this->clickAndWait("//a[@class='pagination_next']");
        }        
        return false;
    }    
    
    public function typeInFrame($selector, $value)
    {
        $this->retry('selectFrame', array($selector));
        $this->s->type("//body", $value);
        $this->s->selectFrame("relative=top");
    }

    public function getLastEmail($match = "Subject")
    {
        return $this->retry('_getLastEmail', array($match));
    }
    
    public function assertNoEmail($match = "Subject")
    {
        try
        {
            $this->_getLastEmail($match);
        }
        catch (Exception $ex) 
        {
            return;
        }
    
        throw new Exception("Found matching email for $match");
    }

    public function _getLastEmail($match = "Subject")
    {
        global $TEST_CONFIG;
        
        $mock_mail_file = $TEST_CONFIG['mock_mail_file'];
        
        if (!file_exists($mock_mail_file))
        {    
            throw new Exception("no emails in file");
        }        
        $contents = file_get_contents($mock_mail_file);
        
        // decode quoted-printable
        $contents = str_replace("=\r\n","", $contents);
        $contents = preg_replace('/\=([A-F0-9][A-F0-9])/','%$1',$contents);
        $contents = rawurldecode($contents);                
        
        $matchPos = strrpos($contents, $match);
        if ($matchPos === false)
        {
            throw new Exception("'$match' not found in email");
        }
        
        $endPos = strpos($contents, '--------', $matchPos);
        if ($endPos === false)
        {
            throw new Exception("full email not yet written to file");
        }        
        
        $startPos = strrpos($contents, "========", $matchPos - strlen($contents));
        if ($startPos === false)
        {
            throw new Exception("email start marker not found");
        }                
        $startPos = strpos($contents, "\n", $startPos) + 1;
        
        $email = substr($contents, $startPos, $endPos - $startPos);                            
                                                        
        return $email;
    }    
    
    public function getLinkFromEmail($email, $index = 0)
    {
        if (!preg_match_all('/http:[^\\s]+/', $email, $matches))
        {
            throw new Exception("couldn't find any links in email $email");
        }
        if ($index >= sizeof($matches[0]))
        {
            throw new Exception("couldn't find link $index in email $email");
        }
        return $matches[0][$index];
    }
    
    public function retry($fn_name, $args = null, $timeout = 15)
    {
        return retry(array($this, $fn_name), $args, $timeout);
    }

    public function selectUploadFrame($xpath = "//iframe[contains(@src,'upload')]")
    {
        // requires the profiles/noflash profile 
        // (selenium can only test upload via normal html file input)
        $this->retry('selectFrame', array($xpath));        
        $this->retry('mouseOver', array("//input[@type='file']"));    
    }    
    
    public function setUrl($url)
    {
        // for some reason open() loads the action twice?
        $this->s->getEval("window.location.href='$url';");
        $this->s->waitForPageToLoad(10000);
    }    
    
    public function checkImage($imgUrl, $minBytes, $maxBytes)
    {
        $imgData = file_get_contents($imgUrl);
        $imgSize = strlen($imgData);
        $this->assertGreaterThan($minBytes, $imgSize);
        $this->assertLessThan($maxBytes, $imgSize);        
        
        $sizeArray = getimagesize($imgUrl);
        
        $this->assertTrue(is_array($sizeArray));
                
        $width = $sizeArray[0];
        $height = $sizeArray[1];
        
        $this->assertGreaterThan(10, $width);
        $this->assertGreaterThan(10, $height);
    }
        
    public function submitFakeCaptcha()
    {
        $answer = $this->getText("//b[@id='captcha_answer']");
        $this->type("//input[@name='captcha_response']", $answer);
        $this->submitForm();
    }
     
    function selectShareWindow()
    {
        $this->selectWindow('eshare');
        $this->mouseOver("//textarea");
    }
     
}


function retry($fn, $args = null, $timeout = 15)
{
    $time = time();
    if (!$args) 
    { 
        $args = array(); 
    }
    
    while (true)
    {
        try
        {
            return call_user_func_array($fn, $args);
        }
        catch (Exception $ex)
        {
        }

        if (time() - $time > $timeout)
        {
            break;
        }

        sleep(0.25);
    }
    return call_user_func_array($fn, $args);
}