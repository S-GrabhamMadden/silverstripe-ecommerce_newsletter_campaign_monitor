<?php

namespace Sunnysideup\EcommerceNewsletterCampaignMonitor\Extensions;

use Sunnysideup\Ecommerce\Forms\OrderFormAddress;
use Sunnysideup\CampaignMonitor\CampaignMonitorSignupPage;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Convert;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use SilverStripe\View\Requirements;
use Sunnysideup\CampaignMonitor\Api\CampaignMonitorAPIConnector;
use Sunnysideup\CampaignMonitor\Api\CampaignMonitorSignupFieldProvider;
use Sunnysideup\Ecommerce\Config\EcommerceConfig;
use Sunnysideup\Ecommerce\Model\Order;

/**
 * Class \Sunnysideup\EcommerceNewsletterCampaignMonitor\Extensions\EcommerceNewsletterCampaignMonitorSignupDecoratorFormFixes
 *
 * @property OrderFormAddress|EcommerceNewsletterCampaignMonitorSignupDecoratorFormFixes $owner
 */
class EcommerceNewsletterCampaignMonitorSignupDecoratorFormFixes extends Extension
{
    private static $fields_to_hide = [
        'FirstName',
        'Surname',
        'SubscribeChoice',
    ];

    /**
     * @var CampaignMonitorAPIConnector
     */
    private static $_api;

    /**
     * @return CampaignMonitorAPIConnector
     */
    public function getAPI()
    {
        if (! self::$_api) {
            self::$_api = CampaignMonitorAPIConnector::create();
            self::$_api->init();
        }

        return self::$_api;
    }

    public function updateFields(FieldList $fields)
    {
        if ($this->hasCampaignMonitorPage()) {
            $page = $this->CampaignMonitorPage();
            if ($page->ReadyToReceiveSubscribtions()) {
                // Create fields
                $member = Security::getCurrentUser();
                if (! $member) {
                    $member = Member::create();
                }

                $campaignMonitorFieldsHolder = CompositeField::create()
                    ->setName('CampaignMonitorFields')
                    ->addExtraClass('campaignMonitorFieldsHolder');
                $signupField = $member->getCampaignMonitorSignupField($page->ListID, 'SubscribeChoice');
                $fieldsToHide = Config::inst()->get(EcommerceNewsletterCampaignMonitorSignupDecoratorFormFixes::class, 'fields_to_hide');
                foreach ($fieldsToHide as $field) {
                    Requirements::customCSS('#CMCustomField' . $field . ' {display: none;}');
                }

                $config = EcommerceConfig::inst();
                if ($config->CampaignMonitorSignupHeader) {
                    $campaignMonitorFieldsHolder->push(HeaderField::create('CampaignMonitorNewsletterSignupHeader', $config->CampaignMonitorSignupHeader, 2));
                }

                if ($config->CampaignMonitorSignupIntro) {
                    $campaignMonitorFieldsHolder->push(LiteralField::create('CampaignMonitorNewsletterSignupContent', '<p class="campaignMonitorNewsletterSignupContent">' . $config->CampaignMonitorSignupIntro . '</p>'));
                }

                $label = $config->CampaignMonitorSignupLabel;
                if (! $label) {
                    $label = _t('EcommerceNewsletterCampaignMonitorSignupDecoratorFormFixes.JOIN', 'Join');
                }

                $campaignMonitorFieldsHolder->push(CheckboxField::create('CampaignMonitorNewsletterSubscribeCheckBox', $label));
                $campaignMonitorFieldsHolder->push($signupField);
                $fields->push($campaignMonitorFieldsHolder);
                Requirements::customCSS('
                    #SubscribeChoice {display: none!important;}
                    .CMFieldsCustomFieldsHolder {display: none!important;}
                ');
                Requirements::customScript('
                window.document.addEventListener(\'DOMContentLoaded\', () => {
                    window.document.querySelector(\'input[name="CampaignMonitorNewsletterSubscribeCheckBox"]\').addEventListener(\'change\', () => {
                        const cmFields = window.document.querySelector(\'.CMFieldsCustomFieldsHolder\');
                        cmFields.style.display = (cmFields.style.display === \'none\' ? \'\' : \'none\');
                    });
                });


                ');
            }
        }
    }

    /**
     * adds the user to the list ...
     *
     * @param array  $data
     * @param Form   $form
     * @param Order  $order
     * @param Member $member
     */
    public function saveAddressExtension($data, $form, ?Order $order = null, ?Member $member = null)
    {
        if (isset($data['CampaignMonitorNewsletterSubscribeCheckBox']) && $data['CampaignMonitorNewsletterSubscribeCheckBox'] && $this->hasCampaignMonitorPage()) {
            $page = $this->CampaignMonitorPage();
            if ($page->ReadyToReceiveSubscribtions()) {
                //true until proven otherwise.
                $newlyCreatedMember = false;
                $isSubscribe = isset($data['SubscribeChoice']) && 'Subscribe' === $data['SubscribeChoice'];
                $member = Security::getCurrentUser();
                if (! $member) {
                    $myEmail = $data['Email'] ?? random_int(0, mt_getrandmax());
                    //$memberAlreadyLoggedIn = false;
                    $existingMember = Member::get()->filter(['Email' => Convert::raw2sql($myEmail)])->First();
                    //if($isSubscribe && $existingMember){
                    //$form->addErrorMessage('Email', _t("CAMPAIGNMONITORSIGNUPPAGE.EMAIL_EXISTS", "This email is already in use. Please log in for this email or try another email address."), 'warning');
                    //$this->redirectBack();
                    //return;
                    //}
                    $member = $existingMember;
                    if (! $member) {
                        $newlyCreatedMember = true;
                        $member = Member::create();
                    }
                }

                //logged in: if the member already as someone else then you can't sign up.
                //$memberAlreadyLoggedIn = true;
                //$existingMember = Member::get()
                //    ->filter(array("Email" => Convert::raw2sql($data["CampaignMonitorEmail"])))
                //    ->exclude(array("ID" => $member->ID))
                //    ->First();
                //if($isSubscribe && $existingMember) {
                //$form->addErrorMessage('CampaignMonitorEmail', _t("CAMPAIGNMONITORSIGNUPPAGE.EMAIL_EXISTS", "This email is already in use by someone else. Please log in for this email or try another email address."), 'warning');
                //$this->redirectBack();
                //return;
                //}

                //if this is a new member then we save the member
                if ($isSubscribe && $newlyCreatedMember) {
                    $form->saveInto($member);
                    $member->Email = Convert::raw2sql($data['Email']);
                    //$member->SetPassword = true;
                    //$member->Password = Member::create_new_password();
                    $member->write();
                }

                $fieldName = Config::inst()->get(CampaignMonitorSignupFieldProvider::class, 'campaign_monitor_signup_fieldname');
                $values = $data[$fieldName] ?? 'error';
                $member->processCampaignMonitorSignupField($page, $data, $values);
            }
        }
    }

    /**
     * returns ID of Mailing List that people are subscribing to.
     *
     * @return CampaignMonitorSignupPage
     */
    protected function hasCampaignMonitorPage()
    {
        $config = EcommerceConfig::inst();

        return (bool) $config->CampaignMonitorSignupPageID;
    }

    /**
     * returns ID of Mailing List that people are subscribing to.
     *
     * @return CampaignMonitorSignupPage
     */
    protected function campaignMonitorPage()
    {
        $config = EcommerceConfig::inst();

        return $config->CampaignMonitorSignupPage();
    }
}
