<?php

    class Init {
        static $check_if_const_defined = true;
        
        public static function define($name, $value) {
            if (self::$check_if_const_defined && defined($name)) {
                throw new Init_Exception('Константа уже объявлена: "' . $name . '"');
            }
            
            return define($name, $value);
        }
        
        public static function setIncludePath($paths = array()) {
            array_unshift($paths, get_include_path());
            
            $paths = implode(PATH_SEPARATOR, $paths);
            $paths .= PATH_SEPARATOR;
            
            return ini_set('include_path', $paths); 
        }
        
        public static function setErrorReporting($level) {
            return ini_set('error_reporting', $level);
        }
                              
        public static function setupErrorHandler() {
            require_once 'ErrorReporter.php';
            
            $class = 'ErrorReporter';
            $callback['error']     = array($class, 'errorHandler');
            $callback['exception'] = array($class, 'exceptionHandler');
            
            set_error_handler($callback['error'], ini_get('error_reporting'));                      
            set_exception_handler($callback['exception']);
        }
        
        public static function setLocale($locale) {
            setlocale(LC_COLLATE, $locale);
            setlocale(LC_CTYPE,   $locale);
            setlocale(LC_TIME,    $locale);    
        }
        
        public static function setTimezone($zone) {
            if(phpversion() >= 5) date_default_timezone_set($zone);
        }
        
        public static function setMaxExecutionTime($sec) {
            ini_set('max_execution_time', $sec);
        }
        
        public static function enableAssertions() {
            assert_options(ASSERT_ACTIVE, 1);
            assert_options(ASSERT_BAIL,   1);
        }
    }

?>
