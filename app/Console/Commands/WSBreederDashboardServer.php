<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use React\Socket\Server;
use React\EventLoop\Factory;
use React\ZMQ\Context;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Wamp\WampServer;
use App\Models\Pusher;

class WSBreederDashboardServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'breeder:serve';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start websocket server for Breeder Dashboard real-time functionalities';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $loop   = Factory::create();
	    $pusher = new Pusher;

	    // Listen for the web server to make a ZeroMQ push after an ajax request
	    $context = new Context($loop);
	    $pull = $context->getSocket(\ZMQ::SOCKET_PULL);
	    $pull->bind('tcp://127.0.0.1:5555'); // Binding to 127.0.0.1 means the only client that can connect is itself
	    $pull->on('message', array($pusher, 'onProductUpdate'));

		$this->info("Starting websocket server for breeder dashboard on port 8080");

	    // Set up our WebSocket server for clients wanting real-time updates
	    $webSock = new Server($loop);
	    $webSock->listen(8080, '0.0.0.0'); // Binding to 0.0.0.0 means remotes can connect
	    $webServer = new IoServer(
	        new HttpServer(
	            new WsServer(
	                new WampServer(
	                    $pusher
	                )
	            )
	        ),
	        $webSock
	    );

	    $loop->run();
    }
}
