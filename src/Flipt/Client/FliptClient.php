<?php

namespace Flipt\Client;

use GuzzleHttp\Client;


class FliptClient {

    protected Client $client;
    protected string $apiToken;
    protected string $namespace;
    protected string $entityId;
    protected array $context;


    public function __construct( string|Client $host, string $apiToken, string $namespace, array $context = [], string $entityId = '' ) {
        $this->apiToken = $apiToken;
        $this->namespace = $namespace;
        $this->context = $context;
        $this->entityId = $entityId;

        $this->client = ( is_string( $host ) ) ? new Client([ 'base_uri' => $host ] ) : $host;
    }


    /**
     * Returns true/false based on the context evaluation
     */
    public function boolean( string $name, $context = [], $entityId = NULL ):bool {

        $response = $this->evaluationRequest( '/evaluate/v1/boolean', $name, $context, $entityId );

        return $response['enabled'];
    }



    /**
     * Returns the variant key of the matching rule
     */
    public function variant( string $name, $context = [], $entityId = NULL ):string {

        $response = $this->evaluationRequest( '/evaluate/v1/variant', $name, $context, $entityId );

        if( $response['match'] ) return $response['variantKey'];
    }


    /**
     * Returns the variant attachment of the matching rule
     */
    public function variantAttachment( string $name, $context = [], $entityId = NULL ):array {

        $response = $this->evaluationRequest( '/evaluate/v1/variant', $name, $context, $entityId );

        if( $response['match'] ) return json_decode( $response['variantAttachment'], true );
    }





    /**
     * Helper function to create an evaluation request based on the client settings
     */
    protected function evaluationRequest( string $path, string $name, $context = [], $entityId = NULL ) {

        return $this->apiRequest( $path, [
            'context' => array_merge( $this->context, $context ),
            'entityId' => isset( $entityId ) ? $entityId : $this->entityId,
            'flagKey' => $name,
            'namespaceKey' => $this->namespace,
        ] );
    }



    /**
     * Helper function to perform a guzzle request with the correct headers and body
     */
    protected function apiRequest( string $path, array $body = [], string $method = 'POST' ) {

        $response = $this->client->request( $method, $path, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Accept' => 'application/json'
            ],
            'body' => json_encode( $body, JSON_FORCE_OBJECT ),
        ]);

        return json_decode($response->getBody(), true);
    }


    /**
     * Create a new client with a different namespace
     */
    public function withNamespace( string $namespace ) {
        return new FliptClient( $this->client, $this->apiToken, $namespace, $this->context, $this->entityId );
    }

    /**
     * Create a new client with a different context
     */
    public function withContext( array $context ) {
        return new FliptClient( $this->client, $this->apiToken, $this->namespace, $context, $this->entityId );
    }
}
