<div id="authloginpass_login_zone">
{if $failed}
<p>{@authloginpass~auth.message.failedToLogin@}</p>
{/if}

{if ! $isAuthenticated}

    <form action="{formurl 'authloginpass~sign:checkCredentials'}" method="post" id="loginForm">
      <fieldset>
      <table>
       <tr>
            <th><label for="login">{@authloginpass~auth.form.login@}</label></th>
            <td><input type="text" name="login" id="login" size="9" value="{$login|eschtml}" /></td>
       </tr>
       <tr>
            <th><label for="password">{@authloginpass~auth.form.password@}</label></th>
            <td><input type="password" name="password" id="password" size="9" /></td>
       </tr>
       </table>
       <a href="{jurl 'authloginpass~password_reset:index'}">{@authloginpass~auth.form.password.forget@}</a>
       {formurlparam 'authloginpass~sign:checkCredentials'}
       <input type="submit" value="{@authloginpass~auth.form.buttons.login@}"/>
       </fieldset>
   </form>
{else}
    <p>{$user->getName()} | <a href="{jurl 'authcore~sign:out'}" >{@authcore~auth.link.logout@}</a></p>
{/if}
</div>
