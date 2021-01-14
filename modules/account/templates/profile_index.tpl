{ifusernotauthenticated}
    <p> {@account~account.profile.index.not.authenticated@} </p>
{else}
    <h3> {@account~account.profile.infos.name@}</h3>
    <table class="table">
    {formcontrols $form}
       <tr>
           <th>{ctrl_label}</th>
           <td>{ctrl_value}</td>
       </tr>
    {/formcontrols}
    </table>
    <a href="{jurl 'account~profile:modify'}" style="color: black;">
        <input type="submit" value="{@account~account.profile.button.modify@}" />
    </a>
{/ifusernotauthenticated}