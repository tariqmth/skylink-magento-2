<?php

namespace RetailExpress\SkyLink\Block\Dev;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use RetailExpress\SkyLink\Model\Verification\GroupSlug;
use RetailExpress\SkyLink\Model\Verification\Verifier as SkyLinkVerifier;

class Verification extends Template
{
    private $skyLinkVerifier;

    public function __construct(
        TemplateContext $templateContext,
        SkyLinkVerifier $skyLinkVerifier,
        array $data = []
    ) {
        $this->skyLinkVerifier = $skyLinkVerifier;

        parent::__construct($templateContext, $data);
    }

    public function hasGroups()
    {
        return count($this->skyLinkVerifier->getGroups()) > 0;
    }

    public function getSortedGroups()
    {
        return $this->skyLinkVerifier->getSortedGroups();
    }

    public function hasChecksForGroupWithSlug(GroupSlug $groupSlug)
    {
        return count($this->skyLinkVerifier->getChecksForGroupWithSlug($groupSlug)) > 0;
    }

    public function getSortedChecksForGroupWithSlug(GroupSlug $groupSlug)
    {
        return $this->skyLinkVerifier->getSortedChecksForGroupWithSlug($groupSlug);
    }

    protected function _prepareLayout()
    {
        $this->skyLinkVerifier->gather();

        return parent::_prepareLayout();
    }
}
