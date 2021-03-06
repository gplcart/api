[![Build Status](https://scrutinizer-ci.com/g/gplcart/api/badges/build.png?b=master)](https://scrutinizer-ci.com/g/gplcart/api/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/gplcart/api/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/gplcart/api/?branch=master)

API is a [GPL Cart](https://github.com/gplcart/gplcart) module that helps to implement WEB API for your GPLCart. Basically it provides a simple authorization mechanism based on [JWT tokens](https://jwt.io) and uses other modules for processing API requests. It does nothing by itself, so do not install unless other modules require it.

**Dependencies**

- [Oauth module](https://github.com/gplcart/oauth)

**Installation**

1. Download and extract to `system/modules` manually or using composer `composer require gplcart/api`. IMPORTANT: If you downloaded the module manually, be sure that the name of extracted module folder doesn't contain a branch/version suffix, e.g `-master`. Rename if needed.
2. Go to `admin/module/list` end enable the module
3. Ajust module settings at `admin/module/settings/api`
4. Grant module permissions to other administrators at `admin/user/role`
5. Add some API users at `admin/user/api`

**Usage**

Look at `example.php` for code samples.
API endpoint - http://yourdomain.com/api. This URL is used for initial login to get an access token. Once you got the token you are able to query API with URL arguments, e.g: http://domain.com/api/arg1/arg2/arg3. You can pass API version as a query parameter: http://yourdomain.com/api/arg1/arg2/arg3?varsion=1.0. Note: there is no way (yet) to refresh access tokens. You should re-login after your access token has expired.

**Processors**

Since the module does not process API requests by itself, you should use another module for this. The processor is responsible for system calls using an array of arguments from URL. This is how the `module.api.process` hook must be implemented in a module:

    public function hookModuleApiProcess(array $params, array $user, &$response, $controller){
    	
		$query = $params['query']; // GET query array
		$version = isset($query['version']) ? $query['version'] : 1; // API version
		
		list($arg1, $arg2) = $params['arguments']; // Exploded path arguments
    
    	if($arg1 === 'products'){
    		$response = array(...); // An array of products to be delivered to a client
    }

By default all data from processors is delivered in JSON format. If you need another format then implement hook `module.api.output`

    public function hookModuleApiOutput(array $arguments, array $user, $response, $controller){
        // Don't forget to set all needed headers
		echo $this->arrayToXml($response);
		exit; // Important. Abort further processing
    }
