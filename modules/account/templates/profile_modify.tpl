{ifusernotauthenticated}
    <p>{@account.profile.modify.not.authenticated@}</p>
{else}
    {form $form, 'account~profile:save'}
        <table class="table">
            {formcontrols}
                <tr>
                    <th>{ctrl_label}</th><td>{ctrl_control}</td>
                </tr>
            {/formcontrols}
        </table>
        {formsubmit}
    {/form}
{/ifusernotauthenticated}