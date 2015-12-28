<?php

namespace M6Web\Bundle\PhpProcessManagerBundle\Bridge;

use React\Http\Request as ReactRequest;
use React\Http\Response as ReactResponse;

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

use Symfony\Component\HttpFoundation\StreamedResponse as SymfonyStreamedResponse;

/**
 * Http Kernel Bridge
 */
class HttpKernel implements BridgeInterface
{
    /**
     * An application implementing the HttpKernelInterface
     *
     * @var \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    protected $application;

    /**
     * @param \Symfony\Component\HttpKernel\HttpKernelInterface $application
     */
    public function __construct(\Symfony\Component\HttpKernel\HttpKernelInterface $application)
    {
        $this->application = $application;
    }

    /**
     * Handle a request
     *
     * @param ReactRequest  $request
     * @param ReactResponse $response
     */
    public function onRequest(ReactRequest $request, ReactResponse $response)
    {
        $content = '';
        $headers = $request->getHeaders();

        $contentLength = isset($headers['Content-Length']) ? (int) $headers['Content-Length'] : 0;

        $request->on('data', function ($data) use ($request, $response, &$content, $contentLength) {
            // Read data (may be empty for GET request)
            $content.= $data;
            // Handle request after receive
            if (strlen($content) >= $contentLength) {
                $symfonyRequest = self::mapRequest($request, $content);

                try {
                    // Execute
                    $symfonyResponse = $this->application->handle($symfonyRequest);
                } catch (\Exception $exception) {
                    // Internal server error
                    $response->writeHead(500);
                    $response->end();

                    return;
                }

                self::mapResponse($response, $symfonyResponse);

                if ($this->application instanceof TerminableInterface) {
                    $this->application->terminate($symfonyRequest, $symfonyResponse);
                }
            }
        });
    }

    /**
     * Convert React\Http\Request to Symfony\Component\HttpFoundation\Request
     *
     * @param ReactRequest $reactRequest
     * @param string       $content
     *
     * @return SymfonyRequest $symfonyRequest
     */
    protected static function mapRequest(ReactRequest $reactRequest, $content)
    {
        $method  = $reactRequest->getMethod();
        $headers = $reactRequest->getHeaders();
        $query   = $reactRequest->getQuery();

        $post = [];

        // Parse body?
        if (in_array(strtoupper($method), ['POST', 'PUT', 'DELETE', 'PATCH']) &&
            isset($headers['Content-Type']) && (0 === strpos($headers['Content-Type'], 'application/x-www-form-urlencoded'))
        ) {
            parse_str($content, $post);
        }

        // Map to a SymfonyRequest
        $symfonyRequest = new SymfonyRequest(
            $query, // $query
            $post, // $request
            array(), // @todo $attributes
            array(), // @todo $cookies
            array(), // @todo $files
            array(), // @todo $server
            $content
        );

        $symfonyRequest->setMethod($method);
        $symfonyRequest->headers->replace($headers);
        $symfonyRequest->server->set('REQUEST_URI', $reactRequest->getPath());

        if (isset($headers['Host'])) {
            $symfonyRequest->server->set('SERVER_NAME', explode(':', $headers['Host'])[0]);
        }

        return $symfonyRequest;
    }

    /**
     * Convert Symfony\Component\HttpFoundation\Response to React\Http\Response
     *
     * @param ReactResponse   $reactResponse
     * @param SymfonyResponse $symfonyResponse
     */
    protected static function mapResponse(ReactResponse $reactResponse, SymfonyResponse $symfonyResponse)
    {
        $headers = $symfonyResponse->headers->all();
        $reactResponse->writeHead($symfonyResponse->getStatusCode(), $headers);

        // @TODO convert StreamedResponse in an async manner
        if ($symfonyResponse instanceof SymfonyStreamedResponse) {
            ob_start();

            $symfonyResponse->sendContent();
            $content = ob_get_contents();

            ob_end_clean();
        } else {
            $content = $symfonyResponse->getContent();
        }

        $reactResponse->end($content);
    }
}
