<?php
namespace GetResponse\GetResponseIntegration\Helper;

/**
 * Class ApiHelper
 * @package GetResponse\GetResponseIntegration\Helper
 */
class ApiHelper
{
    /** @var GetResponseAPI3 */
    public $grApi;

    public function __construct(GetResponseAPI3 $grApi)
    {
        $this->grApi = $grApi;
    }

    /**
     * @param array $user_customs
     * @return array
     */
    public function setCustoms($user_customs)
    {
        $allCustoms = $this->getCustomFields();

        $custom_fields = [];

        if (empty($user_customs)) {
            return $custom_fields;
        }

        foreach ($user_customs as $name => $value) {
            if (in_array($name, array_keys($allCustoms))) {
                $custom_fields[] = [
                    'customFieldId' => $allCustoms[$name],
                    'value'         => [$value]
                ];
            } else {
                $custom = $this->grApi->addCustomField([
                    'name'   => $name,
                    'type'   => "text",
                    'hidden' => "false",
                    'values' => [$value],
                ]);

                if (!empty($custom) && !empty($custom->customFieldId)) {
                    $custom_fields[] = [
                        'customFieldId' => $custom->customFieldId,
                        'value'         => [$value]
                    ];
                }
            }
        }

        return $custom_fields;
    }

    /**
     * Get all user custom fields from gr account
     * @return array
     */
    public function getCustomFields()
    {
        $all_customs = [];
        $results     = $this->grApi->getCustomFields();
        if (!empty($results)) {
            foreach ($results as $ac) {
                if (isset($ac->name) && isset($ac->customFieldId)) {
                    $all_customs[$ac->name] = $ac->customFieldId;
                }
            }
        }

        return $all_customs;
    }

    /**
     * Merge user custom fields selected on WP admin site with those from gr account
     * @param $results
     * @param $user_customs
     *
     * @return array
     */
    public function mergeUserCustoms($results, $user_customs)
    {
        $custom_fields = [];

        if (is_array($results)) {
            foreach ($results as $customs) {
                $value = $customs->value;
                if (in_array($customs->name, array_keys($user_customs))) {
                    $value = [$user_customs[$customs->name]];
                    unset($user_customs[$customs->name]);
                }

                $custom_fields[] = [
                    'customFieldId' => $customs->customFieldId,
                    'value'         => $value
                ];
            }
        }

        return array_merge($custom_fields, $this->setCustoms($user_customs));
    }
}