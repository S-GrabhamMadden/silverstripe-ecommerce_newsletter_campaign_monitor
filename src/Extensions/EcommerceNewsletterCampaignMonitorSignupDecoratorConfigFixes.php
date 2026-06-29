<?php

namespace Sunnysideup\EcommerceNewsletterCampaignMonitor\Extensions;

use SilverStripe\Core\Extension;
use Sunnysideup\Ecommerce\Model\Config\EcommerceDBConfig;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TextField;
use Sunnysideup\CampaignMonitor\CampaignMonitorSignupPage;

/**
 * Class \Sunnysideup\EcommerceNewsletterCampaignMonitor\Extensions\EcommerceNewsletterCampaignMonitorSignupDecoratorConfigFixes
 *
 * @property EcommerceDBConfig|EcommerceNewsletterCampaignMonitorSignupDecoratorConfigFixes $owner
 * @property string $CampaignMonitorSignupHeader
 * @property string $CampaignMonitorSignupIntro
 * @property string $CampaignMonitorSignupLabel
 * @property int $CampaignMonitorSignupPageID
 * @method CampaignMonitorSignupPage CampaignMonitorSignupPage()
 */
class EcommerceNewsletterCampaignMonitorSignupDecoratorConfigFixes extends Extension
{
    private static $db = [
        'CampaignMonitorSignupHeader' => 'Varchar(50)',
        'CampaignMonitorSignupIntro' => 'Varchar(255)',
        'CampaignMonitorSignupLabel' => 'Varchar(30)',
    ];

    private static $has_one = [
        'CampaignMonitorSignupPage' => CampaignMonitorSignupPage::class,
    ];

    public function onBeforeWrite()
    {
        if (! $this->getOwner()->CampaignMonitorSignupPageID) {
            $this->getOwner()->CampaignMonitorSignupHeader = '';
            $this->getOwner()->CampaignMonitorSignupIntro = '';
            $this->getOwner()->CampaignMonitorSignupLabel = '';
        }
    }

    public function updateCMSFields(FieldList $fields)
    {
        $lists = CampaignMonitorSignupPage::get_ready_ones();
        if ($lists->exists()) {
            $options = [0 => _t('EcommerceNewsletterCampaignMonitorSignup.PLEASE_SELECT', '-- please select --')] + $lists->map()->toArray();
            if ($this->getOwner()->CampaignMonitorSignupPageID) {
                $fields->addFieldsToTab(
                    'Root.Newsletter',
                    [
                        DropdownField::create('CampaignMonitorSignupPageID', _t('EcommerceNewsletterCampaignMonitorSignup.SIGN_UP_TO', 'Sign-up for ...'), $options),
                        TextField::create('CampaignMonitorSignupHeader', _t('EcommerceNewsletterCampaignMonitorSignup.HEADER', 'Header')),
                        TextField::create('CampaignMonitorSignupIntro', _t('EcommerceNewsletterCampaignMonitorSignup.INTRO', 'Intro')),
                        TextField::create('CampaignMonitorSignupLabel', _t('EcommerceNewsletterCampaignMonitorSignup.LABEL', 'Label')),
                    ]
                );
            } else {
                $fields->addFieldsToTab(
                    'Root.Newsletter',
                    [
                        DropdownField::create('CampaignMonitorSignupPageID', _t('EcommerceNewsletterCampaignMonitorSignup.SIGN_UP_TO', 'Sign-up for ...'), $options),
                    ]
                );
                $fields->removeFieldsFromTab('Root.Main', ['CampaignMonitorSignupHeader', 'CampaignMonitorSignupIntro', 'CampaignMonitorSignupLabel']);
            }
        } else {
            $fields->addFieldsToTab(
                'Root.Newsletter',
                [
                    LiteralField::create('ListExplanation', '<p class="message warning">
                        ' . _t('EcommerceNewsletterCampaignMonitorSignup.RECOMMENDATION_TO_SETUP', 'It is recommended you set up a Campaign Monitor Page with a valid list to subscribe to.') . '
                        </p>'),
                    TextField::create('CampaignMonitorSignupHeader', _t('EcommerceNewsletterCampaignMonitorSignup.HEADER', 'Header')),
                    TextField::create('CampaignMonitorSignupIntro', _t('EcommerceNewsletterCampaignMonitorSignup.INTRO', 'Intro')),
                    TextField::create('CampaignMonitorSignupLabel', _t('EcommerceNewsletterCampaignMonitorSignup.LABEL', 'Label')),
                ]
            );
            $fields->removeFieldFromTab('Root.Main', 'CampaignMonitorSignupPageID');
        }
    }
}
