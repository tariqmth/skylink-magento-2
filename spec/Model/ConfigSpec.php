<?php

namespace spec\RetailExpress\SkyLink\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use PhpSpec\ObjectBehavior;
use RetailExpress\SkyLink\Model\Config;
use RetailExpress\SkyLink\Sdk\ValueObjects\SalesChannelId;
use ValueObjects\Identity\UUID as Uuid;
use ValueObjects\Number\Integer;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

class ConfigSpec extends ObjectBehavior
{
    private $scopeConfig;

    public function let(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;

        $this->beConstructedWith($this->scopeConfig);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Config::class);
    }

    public function it_returns_the_api_version()
    {
        $this->scopeConfig->getValue('skylink/api/version')->willReturn('2');

        $this->getApiVersion()->sameValueAs(new Integer(2))->shouldBe(true);
    }

    public function it_returns_the_v2_api_url()
    {
        $urlString = 'http://example.com';

        $this->scopeConfig->getValue('skylink/api/version_2_url')->willReturn($urlString);

        $this->getV2ApiUrl()->sameValueAs(Url::fromNative($urlString))->shouldBe(true);
    }

    public function it_returns_the_v2_api_client_id()
    {
        $clientId = new Uuid();

        $this->scopeConfig->getValue('skylink/api/version_2_client_id')->willReturn($clientId->toNative());

        $this->getV2ApiClientId()->sameValueAs($clientId)->shouldBe(true);
    }

    public function it_returns_the_v2_api_username()
    {
        $usernameString = 'my_username';

        $this->scopeConfig->getValue('skylink/api/version_2_username')->willReturn($usernameString);

        $this->getV2ApiUsername()->sameValueAs(new StringLiteral($usernameString))->shouldBe(true);
    }

    public function it_returns_the_v2_api_password()
    {
        $passwordString = 'my_password';

        $this->scopeConfig->getValue('skylink/api/version_2_password')->willReturn($passwordString);

        $this->getV2ApiPassword()->sameValueAs(new StringLiteral($passwordString))->shouldBe(true);
    }

    public function it_returns_the_sales_channel_id()
    {
        $this->scopeConfig->getValue('skylink/general/sales_channel_id')->willReturn('1');

        $this->getSalesChannelId()->sameValueAs(new SalesChannelId(1))->shouldBe(true);
    }
}
