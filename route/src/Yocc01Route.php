<?php
/**
 * @Copyright (c) 2023, SINA.com.
 * All Rights Reserved.
 *
 * Yaf 框架, Dagger 路由 
 *
 * @author      chenchen <chenchen@staff.sina.com>
 * @version     $Id$
 */






/**
 * Yaf 框架, Dagger 路由 
 *
 */
class Yocc01Route implements Yaf\Route_Interface
{



    protected static $info = [];
    protected const FLAGS = ['api', 'iframe', 'interface'];



    /**
     * 自定义路由 
     *
     * @param Yaf\Request_Http $request
     *
     * @return boolean 
     */
    public function route($request)
    {
        self::parser();

        if (self::isSmooth()) {
            $request->setBaseUri(self::$info['base']);
            $request->setModuleName(self::$info['module']);
            $request->setControllerName(self::$info['state']);
            $request->setActionName(self::$info['action']);
            return true;
        }

        return false;
    }



    /**
     * 解析 $_SERVER['REQUEST_URI'] 
     *
     * desc: 优先用配置从左到右找到的第一个 module 为准
     * http(s)://domain/base/path?query
     * base: /nn/mm
     * path: /aaa/flag/bbb/ccc
     * path.module: aaa
     * path.flag: flag, (flag 属于事先声明 conf/application.ini, application.modules)
     * path.controller: bbb
     * path.action: ccc
     * query: zz=1&yy=2&xx=3
     *
     * @return boolean 
     */
    private static function parser()
    {
        // $_SERVER['REQUEST_URI'] ~= '/'
        if (empty($_SERVER['REQUEST_URI']) || $_SERVER['REQUEST_URI'] === '/') {
            return false;
        }
        
        // path, query
        $path = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        $query = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY));
        self::$info['path']  = rtrim($path, '/'); 
        self::$info['query'] = trim($query); 

        // conf/application.ini, application.modules, 从配置找到 module 节
        $modules = Yaf\Application::app()->getModules();
        foreach ($modules as $k => $v) {
            // module, base
            $pos = strpos($path, lcfirst($v));
            if ($pos === false) {
                continue;
            }
            self::$info['module'] = $v;
            self::$info['base'] = rtrim(substr($path, 0, $pos), '/'); 

            // controller, action
            $torf  = explode('/', trim(substr($path, $pos + strlen($v)), '/'));
            if ((count($torf) === 2)) {
                self::$info['sflag']  = '';
                self::$info['state']  = ucfirst($torf[0]);
                self::$info['action'] = $torf[1];
                return true;
            }
            if ((count($torf) === 3) && in_array($torf[0], self::FLAGS)) {
                self::$info['sflag']  = $torf[0];
                self::$info['state']  = ucfirst($torf[1]);
                self::$info['action'] = $torf[2];
                return true;
            }
            break;
        }

        return false;
    }



    /**
     * 是否路由合理
     *
     * @return bool 
     */
    private static function isSmooth()//: boolean
    {
        self::$info['isSmooth'] = false;

        if ((!isset(self::$info['module']) || self::$info['module'] ==='')
            || (!isset(self::$info['state']) || self::$info['state'] ==='')
            || (!isset(self::$info['action']) || self::$info['action'] ==='')) {
            return false;
        }

        $modus = Yaf\Application::app()->getModules();
        if (!in_array(self::$info['module'], $modus)) {
            return false;
        }

        self::$info['isSmooth'] = true;

        return true;
    }


    
    /**
     * 组合 url 
     * https://www.php.net/manual/zh/yaf-route-rewrite.assemble.php 
     *
     * @param array $x
     * @param string $y
     *
     * @return bool 
     */
    public function assemble(array $info, ?array $query = NULL)
    {
    }
}
