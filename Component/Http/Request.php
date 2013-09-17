<?php
namespace SlimeFramework\Component\Http;

class Request
{
    /**
     * Constructor.
     *
     * @param array  $aQuery      The GET parameters
     * @param array  $aRequest    The POST parameters
     * @param array  $aAttribute  The request attributes (parameters parsed from the PATH_INFO, ...)
     * @param array  $aCookie     The COOKIE parameters
     * @param array  $aFile       The FILES parameters
     * @param array  $aServer     The SERVER parameters
     * @param string $sContent    The raw body data
     *
     * @api
     */
    public function __construct(
        array $aQuery = array(),
        array $aRequest = array(),
        array $aAttribute = array(),
        array $aCookie = array(),
        array $aFile = array(),
        array $aServer = array(),
        $sContent = null
    ) {
        $this->aQuery      = $aQuery;
        $this->aRequest    = $aRequest;
        $this->aAttributes = $aAttribute;
        $this->aCookie     = $aCookie;
        $this->aFile       = $aFile;
        $this->aServer     = $aServer;

        $this->sContent    = $sContent;
    }

    /**
     * Creates a new request with values from PHP's super globals.
     *
     * @return Request A new request
     */
    public function createFromGlobals()
    {
        if (strpos($_SERVER['HTTP_CONTENT_TYPE'], 'application/x-www-form-urlencoded') === 0
            && array_key_exists(
                strtoupper($_SERVER['HTTP_REQUEST_METHOD']),
                array('PUT' => true, 'DELETE' => true, 'PATCH' => true)
            )
        ) {
            parse_str(file_get_contents('php://input'), $aRequest);
        } else {
            $aRequest = $_POST;
        }


        return new self($_GET, $_POST, $aRequest, $_COOKIE, $_FILES, $_SERVER);
    }

    /**
     * Creates a Request based on a given URI and configuration.
     *
     * The information contained in the URI always take precedence
     * over the other information (server and parameters).
     *
     * @param string $sURI        The URI
     * @param string $sMethod     The HTTP method
     * @param array $aParameter  The query (GET) or request (POST) parameters
     * @param array $aCookie     The request cookies ($_COOKIE)
     * @param array $aFile       The request files ($_FILES)
     * @param array $aServer     The server parameters ($_SERVER)
     * @param string $sContent    The raw body data
     *
     * @return Request A Request instance
     *
     * @api
     */
    public static function create(
        $sURI,
        $sMethod = 'GET',
        $aParameter = array(),
        $aCookie = array(),
        $aFile = array(),
        $aServer = array(),
        $sContent = null
    ) {
        $aServer = array_replace(
            array(
                'SERVER_NAME'          => 'localhost',
                'SERVER_PORT'          => 80,
                'HTTP_HOST'            => 'localhost',
                'HTTP_USER_AGENT'      => 'Slime-Http/1.X',
                'HTTP_ACCEPT'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'HTTP_ACCEPT_LANGUAGE' => 'en-us,en;q=0.5',
                'HTTP_ACCEPT_CHARSET'  => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
                'REMOTE_ADDR'          => '127.0.0.1',
                'SCRIPT_NAME'          => '',
                'SCRIPT_FILENAME'      => '',
                'SERVER_PROTOCOL'      => 'HTTP/1.1',
                'REQUEST_TIME'         => time(),
            ),
            $aServer
        );

        $aServer['PATH_INFO']      = '';
        $aServer['REQUEST_METHOD'] = strtoupper($sMethod);

        $aComponent = parse_url($sURI);
        if (isset($aComponent['host'])) {
            $aServer['SERVER_NAME'] = $aComponent['host'];
            $aServer['HTTP_HOST']   = $aComponent['host'];
        }

        if (isset($aComponent['scheme'])) {
            if ('https' === $aComponent['scheme']) {
                $aServer['HTTPS']       = 'on';
                $aServer['SERVER_PORT'] = 443;
            } else {
                unset($aServer['HTTPS']);
                $aServer['SERVER_PORT'] = 80;
            }
        }

        if (isset($aComponent['port'])) {
            $aServer['SERVER_PORT'] = $aComponent['port'];
            $aServer['HTTP_HOST']   = $aServer['HTTP_HOST'] . ':' . $aComponent['port'];
        }

        if (isset($aComponent['user'])) {
            $aServer['PHP_AUTH_USER'] = $aComponent['user'];
        }

        if (isset($aComponent['pass'])) {
            $aServer['PHP_AUTH_PW'] = $aComponent['pass'];
        }

        if (!isset($aComponent['path'])) {
            $aComponent['path'] = '/';
        }

        switch (strtoupper($sMethod)) {
            case 'POST':
            case 'PUT':
            case 'DELETE':
                if (!isset($aServer['CONTENT_TYPE'])) {
                    $aServer['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
                }
            case 'PATCH':
                $aRequest = $aParameter;
                $aQuery   = array();
                break;
            default:
                $aRequest = array();
                $aQuery   = $aParameter;
                break;
        }

        $queryString = '';
        if (isset($aComponent['query'])) {
            parse_str(html_entity_decode($aComponent['query']), $qs);

            if ($aQuery) {
                $aQuery      = array_replace($qs, $aQuery);
                $queryString = http_build_query($aQuery, '', '&');
            } else {
                $aQuery      = $qs;
                $queryString = $aComponent['query'];
            }
        } elseif ($aQuery) {
            $queryString = http_build_query($aQuery, '', '&');
        }

        $aServer['REQUEST_URI']  = $aComponent['path'] . ('' !== $queryString ? '?' . $queryString : '');
        $aServer['QUERY_STRING'] = $queryString;
        return new self($aQuery, $aRequest, array(), $aCookie, $aFile, $aServer, $sContent);
    }
}