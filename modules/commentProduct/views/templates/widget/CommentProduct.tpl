{if $comments }
    {foreach from=$comments item=element}
        <p>
            {$element.comment} | <a href="mailto:{$element.email}"> {$element.firstname}</a>
        </p>
    {/foreach}
{/if}
{if $messageResult }
    <div class="alert alert-success" role="alert">
  <p class="alert-text">
    Thank you for your comment
  </p>
</div>
{/if}

<form action="{$smarty.server.REQUEST_URI}" method="post">
<div class="form-group">
  <label class="form-control-label" for="comment">Type your message</label>
  <textarea name="comment" class="form-control" id="comment" cols="30" rows="10"></textarea>
</div>
    
    <br>
    <input type="submit" class="btn btn-outline-primary" value="Submit">
</form>