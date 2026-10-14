<?php

namespace Jelix\Authentication\Core;

class IdentityAttributes
{
    const UserName = 'jelix/identity/username';
    const DisplayName = 'jelix/identity/displayname';
    const FirstName = 'jelix/identity/givenname';
    const LastName = 'jelix/identity/surname';
    const Email = 'jelix/identity/emailaddress';
    const Street = 'jelix/identity/streetaddress';
    const City = 'jelix/identity/locality';
    const StateOrProvince = 'jelix/identity/stateorprovince';
    const PostCode = 'jelix/identity/postalcode';
    const Country = 'jelix/identity/country';
    const PrimaryPhone = 'jelix/identity/homephone';
    const SecondaryPhone = 'jelix/identity/otherphone';
    const MobilePhone = 'jelix/identity/mobilephone';
    const DateOfBirth = 'jelix/identity/dateofbirth';
    const Gender = 'jelix/identity/gender';
    const Webpage = 'jelix/identity/webpage';

    static function getAttributes()
    {
        return [
            self::UserName,
            self::DisplayName,
            self::FirstName,
            self::LastName,
            self::Email,
            self::Street,
            self::City,
            self::StateOrProvince,
            self::PostCode,
            self::Country,
            self::PrimaryPhone,
            self::SecondaryPhone,
            self::MobilePhone,
            self::DateOfBirth,
            self::Gender,
            self::Webpage,
        ];
    }

    /**
     * Returns labels of given attributes.
     *
     * Attributes not known are returned as is.
     *
     * @param array $attributes list of attributes
     * @return array keys are attributes, values are labels
     * @throws \jExceptionSelector
     */
    static function getAttributeLabels(array $attributes)
    {
        $attr = self::getAttributes();
        $labels = [];
        foreach($attributes as $attribute)
        {
            if (in_array($attribute, $attr)) {
                $labels[$attribute] = \jLocale::get('authcore~auth.identity.attributes.'.str_replace('jelix/identity/', '', $attribute));
            }
            else {
                $labels[$attribute] = $attribute;
            }
        }
        return $labels;
    }

}
