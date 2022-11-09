<?php

/**
 * conjoon
 * php-lib-conjoon
 * Copyright (C) 2022 Thorsten Suckow-Homberg https://github.com/conjoon/php-lib-conjoon
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without restriction,
 * including without limitation the rights to use, copy, modify, merge,
 * publish, distribute, sublicense, and/or sell copies of the Software,
 * and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
 * DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE
 * USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

declare(strict_types=1);

namespace Conjoon\Http;

/**
 * HTTP request methods.
 * Descriptions partially copied from https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods.
 *
 * @see https://httpwg.org/specs/rfc9110.html#methods
 */
enum RequestMethod: string
{
    /**
     * The GET method requests a representation of the specified resource.
     * Requests using GET should only retrieve data.
     */
    case GET = "GET";


    /**
     * The HEAD method asks for a response identical to a GET request, but without the response body.
     */
    case HEAD = "HEAD";


    /**
     * The POST method submits an entity to the specified resource, often causing a change in state or
     * side effects on the server.
     */
    case POST = "POST";


    /**
     * The PUT method replaces all current representations of the target resource with the request payload.
     */
    case PUT = "PUT";


    /**
     * The DELETE method deletes the specified resource.
     */
    case DELETE = "DELETE";


    /**
     * The CONNECT method establishes a tunnel to the server identified by the target resource.
    */
    case CONNECT = "CONNECT";


    /**
     * The OPTIONS method describes the communication options for the target resource.
     */
    case OPTIONS = "OPTIONS";


    /**
     * The TRACE method performs a message loop-back test along the path to the target resource.
     */
    case TRACE = "TRACE";


    /**
     * The PATCH method applies partial modifications to a resource.
     */
    case PATCH = "PATCH";
}
