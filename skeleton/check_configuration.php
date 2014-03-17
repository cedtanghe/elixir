<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once dirname(__FILE__) . '/application/vendor/elixir/src/framework/PHPChecker/Collection.php';
require_once dirname(__FILE__) . '/application/vendor/elixir/src/framework/PHPChecker/Requirement.php';
require_once dirname(__FILE__) . '/application/vendor/elixir/src/framework/PHPChecker/Factory.php';

$collection = new PHPChecker_Collection();

/************ PHP VERSION ************/

$PHPVersion = phpversion();

$collection->add(
    PHPChecker_Factory::createRequirement(
        version_compare($PHPVersion, '5.3.3', '>='),
        'PHP version.',
        sprintf('Your PHP version is : %s.', $PHPVersion),
        sprintf('Your PHP version (%s) is less than the required version.', $PHPVersion),
        'PHP version 5.4 and higher is recommended.'
    )
);

/************ INI ************/

$collection->add(
    PHPChecker_Factory::createRequirement(
        ini_get('date.timezone') != false,
        'php.ini configuration (date.timezone).',
        'php.ini configuration success.',
        'Set the "date.timezone" setting in php.ini (like Europe/Paris).'
    )
);

$collection->add(
    PHPChecker_Factory::createRequirement(
        ini_get('detect_unicode') == false,
        'php.ini configuration (detect_unicode).',
        'php.ini configuration success.',
        'detect_unicode should be off in php.ini.'
    )
);

/************ JSON ************/

$collection->add(
    PHPChecker_Factory::createRequirement(
        function_exists('json_encode'),
        'JSON extension.',
        'JSON is available.',
        'Please install the JSON extension.'
    )
);

/************ SESSION ************/

$collection->add(
    PHPChecker_Factory::createRequirement(
        function_exists('session_start'),
        'SESSION.',
        'SESSION is available.',
        'Please install the SESSION extension.'
    )
);

/************ PCRE ************/

$PCREVersion = defined('PCRE_VERSION') ? (float)PCRE_VERSION : null;

$collection->add(
    PHPChecker_Factory::createRequirement(
        null !== $PCREVersion,
        'PCRE extension.',
        'PCRE is available.',
        'Please install the PCRE extension.'
    )
);

/************ PDO ************/

$collection->add(
    PHPChecker_Factory::createRequirement(
        class_exists('PDO'),
        'PDO.',
        'PDO is available.',
        'Please install PDO.'
    )
);

/************ XML ************/

$collection->add(
    PHPChecker_Factory::createRequirement(
        function_exists('simplexml_load_file'),
        'SimpleXML extension.',
        'SimpleXML is available.',
        'Please install the SimpleXML extension.'
    )
);

$collection->add(
    PHPChecker_Factory::createRequirement(
        class_exists('DomDocument'),
        'PHP-XML module (DomDocument).',
        'PHP-XML module is available.',
        'Please install the PHP-XML module.'
    )
);

$collection->add(
    PHPChecker_Factory::createRequirement(
        function_exists('utf8_decode'),
        'XML extension.',
        'XML is available.',
        'Please install the XML extension.'
    )
);

/************ EMAIL ************/

$collection->add(
    PHPChecker_Factory::createRequirement(
        function_exists('mail'),
        'Mail.',
        'Mail is available.',
        'Please configure sending email.',
        'It takes a real sending test to confirm the result'
    )
);

/************ CURL ************/

$collection->add(
    PHPChecker_Factory::createRequirement(
        extension_loaded('curl') && function_exists('curl_init'),
        'cURL.',
        'cURL is available.',
        'Please install the cURL extension.'
    )
);

/************ GD ************/

$collection->add(
    PHPChecker_Factory::createRequirement(
        extension_loaded('gd') && function_exists('gd_info'),
        'GD library.',
        'GD is available.',
        'Please install the GD extension.'
    )
);

/************ FILEINFO (OPTIONAL) ************/

$collection->add(
    PHPChecker_Factory::createRecommendation(
        function_exists('finfo_open'),
        'FileInfo extension.',
        'FileInfo is available.',
        'Please install the FileInfo extension.'
    )
);

/************ CTYPE (OPTIONAL) ************/

$collection->add(
    PHPChecker_Factory::createRecommendation(
        function_exists('ctype_alpha'),
        'ctype.',
        'ctype is available.',
        'Please install the ctype extension.'
    )
);

/************ MBSTRING (OPTIONAL) ************/

$collection->add(
    PHPChecker_Factory::createRecommendation(
        function_exists('mb_strlen'),
        'mbstring.',
        'mbstring is available.',
        'Please install the mbstring extension.'
    )
);

/************ INTL (OPTIONAL) ************/

$collection->add(
    PHPChecker_Factory::createRecommendation(
        extension_loaded('intl') && class_exists('\Locale'),
        'intl extension.',
        'intl is available.',
        'Please install the intl extension.'
    )
);

/************ ICONV (OPTIONAL) ************/

$collection->add(
    PHPChecker_Factory::createRecommendation(
        function_exists('iconv'),
        'iconv.',
        'iconv is available.',
        'Please install the iconv extension.'
    )
);

/************ APC (OPTIONAL) ************/

$collection->add(
    PHPChecker_Factory::createRecommendation(
        extension_loaded('apc') && ini_get('apc.enabled'),
        'APC.',
        'APC is available.',
        'Please install the APC extension.'
    )
);

/************ INI (OPTIONAL) ************/

$collection->add(
    PHPChecker_Factory::createRecommendation(
        ini_get('short_open_tag') == false,
        'php.ini configuration (short_open_tag).',
        'php.ini configuration success.',
        'short_open_tag should be off in php.ini.'
    )
);

$collection->add(
    PHPChecker_Factory::createRecommendation(
        ini_get('magic_quotes_gpc') == false,
        'php.ini configuration (magic_quotes_gpc).',
        'php.ini configuration success.',
        'magic_quotes_gpc should be off in php.ini.'
    )
);

$collection->add(
    PHPChecker_Factory::createRecommendation(
        ini_get('register_globals') == false,
        'php.ini configuration (register_globals).',
        'php.ini configuration success.',
        'register_globals should be off in php.ini.'
    )
);

$collection->add(
    PHPChecker_Factory::createRecommendation(
        ini_get('session.auto_start') == false,
        'php.ini configuration (session.auto_start).',
        'php.ini configuration success.',
        'session.auto_start should be off in php.ini.'
    )
);

$collection->execute();

?>

<!DOCTYPE html>
    <head>
        <title>Testing PHP configuration.</title>
        <style type="text/css">
            body
            {
                font-family: Arial, Helvetica, sans-serif;
            }
            
            .container
            {
                width: 800px;
                margin: 50px auto 25px auto;
            }
			
            .container h1
            {
                color: #333;
                text-align:center;
            }
			
            .dashboard
            {
                color: #333;
                padding: 15px;
                margin-bottom: 15px;
                text-align: center;
            }

            .dashboard h2
            {
                color: #333;
            }
            
            .bloc
            {
                margin: 0 15px 25px 15px;
                padding: 15px;
                background-color: #fff;
                border: 1px solid #ddd;
                border-radius: 4px;
            }
			
            .bloc .title
            {
                color: #333;
            }
			
            .bloc .type
            {
                display:block;
                text-align: right;
            }
            
            .bloc.requirement .type
            {
                color: #a94442;
            }
            
            .bloc.recommendation .type
            {
                color: #31708f;
            }
            
            .bloc p
            {
                padding: 15px;
                border: 1px solid transparent;
                border-radius: 4px;
            }
            
            .bloc p.success
            {
                background-color: #dff0d8;
                border-color: #d6e9c6;
                color: #3c763d;
            }
            
            .bloc p.fail
            {
                background-color: #f2dede;
                border-color: #ebccd1;
                color: #a94442;
            }
            
            .bloc p.help
            {
                background-color: #d9edf7;
                border-color: #bce8f1;
                color: #31708f;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Testing PHP configuration.</h1>
			
			<div class="dashboard">
				<h2>Result assertions : <?php echo count($collection->getSuccessRequirements()) . ' / ' . count($collection->gets(false)); ?>.</h2>
			</div>
			
            <?php foreach($collection as $requirement): ?>
				
                <div class="bloc <?php echo !$requirement->isOptional() ? 'requirement' : 'recommendation'; ?>">
					<span class="type"><?php echo !$requirement->isOptional() ? 'required' : 'optional'; ?></span>
                    <h3 class="title"><?php echo $requirement->getAssertMessage(); ?></h3>
                    
                    <p class="<?php echo $requirement->isSuccess() ? 'success' : 'fail'; ?>">
                        <?php echo $requirement->isSuccess() ? $requirement->getSuccessMessage() : $requirement->getFailMessage(); ?>
                    </p>
                    
                    <?php if(null !== $requirement->getHelpMessage()): ?>
                    <p class="help"><?php echo $requirement->getHelpMessage(); ?></p>
                    <?php endif; ?>
                </div>

            <?php endforeach; ?>
        <div>
    </body>
</html>