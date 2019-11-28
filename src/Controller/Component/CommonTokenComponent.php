<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

class CommonTokenComponent extends Component{
/**
* This function is used to upload image into server
*
* @access public
*
* @param array $imageArr
* @return array
*/
	public function base64url_encode($data) { 
	    return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
	}

	public function jsonWebToken(){
	 $your_private_key_from_google_api_console = "-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQCWKYCAFysFdSv7\nUk0EEhx8V8rbFkSVbXpgOw2xZTji0+5qqVEG5XhDI68SjbGTZxj5+YkkccWnXC9c\niOm6TLLmmVxKL4r9VDSIkPvAuq8t3a783cdsM7MFSvT1cw7F01w5ucF568HkOb36\nBEIkyNyRBwQe0IGPRd0Iog25VRw6XWa4+29BV1PETHrW/pInEzH+dNk3mWCDSLQp\nhL/wyEBFSCQyeT0/m4KdEhjYXyl6rT/b+0TMY5jGOx+T+30wRpSEemNXC+Z4y0Aw\nWJVItRuu7rc3rVqOROz6lHH5tTWGo4eh9bynGeYeXdlzTNwDrfL27rGFT0Z6ekik\nSFGyn20HAgMBAAECggEAH7pqL+Z1TSRqZCKKKmOHSFCmiK9GU4p70ox3wrVgFTFx\njXD0MXBX/luyWMm+rSYaFWt/6kbt3ARy72rwc9BT9ryNzxEHnapmpTp5L0pizF6s\nZDqaMgckeuhRJPGoO+2CbaINuuyxHb+DoCm5LhuQ20XvvXESBwtcfj/7hV7x5XTW\n1NHl4KXCnURcVkCVqgg4ukFh1f0AZhm3n6vCmqArxIw5+4oXuZ/MepdrfdPZxccC\nP32tV/a/ckmL8T0I0WiorM5NC0vgkQNm1c9XqswKaBnn5YY2yQT7Jg331mpUMg81\nKpOPom0/B5Ph0RmGSn05A98yghxoM6+Jnq8pt0p8jQKBgQDN3zBT3eSjhUx8/afR\nrHcUHlIaorIgFZf308U5Mqra7zkAWunHeCQE2En91iAcGsHIX4uDUJJTwP7uZf8p\n4NM+zyQzlLkKksjLy798zTeJ2FeqCdpMcy78oTIPgkL0lDwzG+KZbF0N2+lMp+D/\nAm9dw17zkSZMGo1CrNDg69s6mwKBgQC6ubRtzZbOsZ7ST05z5N3TAzHIMoSdqo6V\n/Ezt1CozO2taYh6n/HDqzr0kIKkVRoqCvqZCQL2CMY7ctPN6ctXSvxbMDm+9BI9i\ncoP5fHdrQMiKnX5g5Vt3OJhli/3bXyMi6IrDgOX9FHOnwiTNCn87asdMRVtglo9f\nDUauw7ZYBQKBgEYnoXNi8TAcE6WgVtjnuah8cKQs/yBZ23CTlOjZ8EktLjKFyJa1\nxSh1gDllB4osQA8FKCi2gzbRVM5uqZZey/3iXsbJDomg3ZY9N2LTF/L8a1tBgkd9\nFOz4DXQlgqWQAje0b/Kyb79ySj1aFB0yejrsgeXkd7WFcs/ezktndEyzAoGABp/R\n89dIU1rfzIw9t0TWTccePAD2zXUgi9egjwto19pyy4kRl1oQU3Q0J5T1Cqku/sZu\ntYkAcB77935/6McsKMbYszKL6kPAJnjzj6VHw0lQFALUWfGpFgiu92NJBUBRycpA\nKgFnp1vTYo8zqQaHTv9RxLXtMPcGbRPWfxq4XFUCgYEAyRmOTlTPAu03hkxYKrfz\njrVVo8nLbhY5RR30Sx11glPOSnYLBMPtmURgBQv9R3/hbzn25S6gn8iIAxUVUgPQ\nMTCRXIHf8GG11itkndlLOOmSiHb0pf6GAweQX05wuLzDFu9E43KUF1Rx+vvT0xIC\nSPr1jwB2q6tDfWc9pnzU8WM=\n-----END PRIVATE KEY-----\n";
	 //Google's Documentation of Creating a JWT: https://developers.google.com/identity/protocols/OAuth2ServiceAccount#authorizingrequests
	 //{Base64url encoded JSON header}
	 $jwtHeader = $this->base64url_encode(json_encode(array(
	    "alg" => "RS256",
	    "typ" => "JWT"
	)));
	//{Base64url encoded JSON claim set}
	$now = time();
	$jwtClaim = $this->base64url_encode(json_encode(array(
	    "iss" => "greenwarriorrestaurant@appspot.gserviceaccount.com",
	    "scope" => "https://www.googleapis.com/auth/androidpublisher",
	    "aud" => "https://www.googleapis.com/oauth2/v4/token",
	    "exp" => $now + 3600,
	    "iat" => $now
	)));
	$jwtSig = "";
	//The base string for the signature: {Base64url encoded JSON header}.{Base64url encoded JSON claim set}
	openssl_sign(
	    $jwtHeader.".".$jwtClaim,
	    $jwtSig,
	    $your_private_key_from_google_api_console,
	    "sha256WithRSAEncryption"
	);
	$jwtSign = $this->base64url_encode($jwtSig);
	$jwtAssertion = $jwtHeader.".".$jwtClaim.".".$jwtSign;
	//{Base64url encoded JSON header}.{Base64url encoded JSON claim set}.{Base64url encoded signature}
	return $jwtAssertion;
}
	public function oauth2accessToken() {
        $jwtAssertion  = $this->jsonWebToken();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.googleapis.com/oauth2/v4/token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array(
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwtAssertion
        ));
        $data = curl_exec($ch);
        $jsonData = json_decode($data, true);
        $access_token = $jsonData['access_token'];
        return $access_token;
    }
}