{ifusernotauthenticated}
    <p> {@account~account.profile.index.not.authenticated@} </p>
{else}
    <h3> {@account~account.profile.infos.name@}</h3>
    <table class="table">
    {formcontrols $form}
       <tr>
           <th>{ctrl_label}</th>
           <td>{ifctrl 'create_date'}
               {ctrl_value_assign 'createdate'}{$createdate|jdatetime:'db_datetime':'lang_datetime'}
               {else}
                {ctrl_value}
               {/ifctrl}
           </td>
       </tr>
    {/formcontrols}
    </table>
    <a href="{jurl 'account~profile:modify'}" class="btn btn-primary">
        {@account~account.profile.button.modify@}
    </a>
{/ifusernotauthenticated}