{ifusernotauthenticated}
    <p>{@account.profile.modify.not.authenticated@}</p>
{else}
    {form $form, 'account~profile:save'}
        <table class="table">
            {formcontrols}
                <tr>
                    <th>{ctrl_label}</th><td>{ifctrl 'create_date'}
                        {ctrl_value_assign 'createdate','',true}{$createdate|jdatetime:'db_datetime':'lang_datetime'}
                        {else}
                        {ctrl_control}
                        {/ifctrl}</td>
                </tr>
            {/formcontrols}
        </table>
        {formsubmit}
    {/form}
{/ifusernotauthenticated}