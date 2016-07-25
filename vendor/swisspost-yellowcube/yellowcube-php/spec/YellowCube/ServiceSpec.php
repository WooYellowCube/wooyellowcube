<?php

namespace spec\YellowCube;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use YellowCube\ART\Article;
use YellowCube\WAB\Order;
use YellowCube\Util\SoapClient;

class ServiceSpec extends ObjectBehavior
{
    function let(SoapClient $client, LoggerInterface $logger) {
        $this->beConstructedWith(null, $client, $logger);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('YellowCube\Service');
    }

    function it_asserts_config_is_given()
    {
        $this->shouldThrow('PhpSpec\Exception\Example\ErrorException')->during('__construct', array(''));
    }

    function it_should_insert_article(Article $article, $client, $logger) {
        $this->insertArticleMasterData($article);

        $client->InsertArticleMasterData(
            Argument::withEntry('ControlReference', Argument::type('YellowCube\ControlReference'))
        )->shouldHaveBeenCalled();
        $logger->info(Argument::any(), Argument::any())->shouldHaveBeenCalled();
    }

    function it_should_return_article_status($client, $logger) {
        $this->getInsertArticleMasterDataStatus('reference-no');

        $client->GetInsertArticleMasterDataStatus(Argument::allOf(
            Argument::withEntry('ControlReference', Argument::type('YellowCube\ControlReference')),
            Argument::withEntry('Reference', 'reference-no')
        ))->shouldHaveBeenCalled();
        $logger->info(Argument::any(), Argument::any())->shouldHaveBeenCalled();
    }

    function it_should_create_customer_order(Order $order, $client, $logger) {
        $this->createYCCustomerOrder($order);

        $client->CreateYCCustomerOrder(Argument::allOf(
            Argument::withEntry('ControlReference', Argument::type('YellowCube\ControlReference')),
            Argument::withEntry('Order', Argument::type('YellowCube\WAB\Order'))
        ))->shouldHaveBeenCalled();
        $logger->info(Argument::type('string'), Argument::any())->shouldHaveBeenCalled();
    }

    function it_should_return_order_status($client, $logger) {
        $this->getYCCustomerOrderStatus('reference-no');

        $client->GetYCCustomerOrderStatus(Argument::allOf(
            Argument::withEntry('ControlReference', Argument::type('YellowCube\ControlReference')),
            Argument::withEntry('Reference', 'reference-no')
        ))->shouldHaveBeenCalled();
        $logger->info(Argument::any(), Argument::any())->shouldHaveBeenCalled();
    }

    function it_should_return_order_replies($client, $logger) {
        $this->getYCCustomerOrderReply()->shouldReturn(array());

        $client->GetYCCustomerOrderReply(Argument::allOf(
            Argument::withEntry('ControlReference', Argument::type('YellowCube\ControlReference'))
        ))->shouldHaveBeenCalled();
        $logger->info(Argument::type('string'), Argument::any())->shouldHaveBeenCalled();
    }

    function it_should_return_inventory($client, $logger) {
        $this->getInventory()->shouldReturn(array());

        $client->GetInventory(Argument::allOf(
            Argument::withEntry('ControlReference', Argument::type('YellowCube\ControlReference'))
        ))->shouldHaveBeenCalled();
        $logger->info(Argument::type('string'), Argument::any())->shouldHaveBeenCalled();
    }
}

