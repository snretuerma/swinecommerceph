<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

class Pusher extends Model implements WampServerInterface
{
    /**
     * A lookup of all the topics clients have subscribed to
     */
    protected $subscribedTopics = [];

    public function onSubscribe(ConnectionInterface $conn, $topic)
	{
		$this->subscribedTopics[$topic->getId()] = $topic;
    }

	/**
     * @param string JSON'ified string we'll receive from ZeroMQ
     */
    public function onProductUpdate($product)
    {
        $productData = json_decode($product, true);

        // If the lookup topic object isn't set there is no one to publish to
        if (!array_key_exists($productData['topic'], $this->subscribedTopics)) {
            return;
        }

        $topic = $this->subscribedTopics[$productData['topic']];

        // re-send the data to all the clients subscribed to that category
        $topic->broadcast($productData);
    }

	public function onUnSubscribe(ConnectionInterface $conn, $topic)
	{

    }

	public function onOpen(ConnectionInterface $conn)
	{

    }

	public function onClose(ConnectionInterface $conn)
	{

    }

	public function onCall(ConnectionInterface $conn, $id, $topic, array $params)
	{
        // In this application if clients send data it's because the user hacked around in console
        $conn->callError($id, $topic, 'You are not allowed to make calls')->close();
    }

	public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible)
	{
        // In this application if clients send data it's because the user hacked around in console
        $conn->close();
    }

	public function onError(ConnectionInterface $conn, \Exception $e)
	{

    }
}
