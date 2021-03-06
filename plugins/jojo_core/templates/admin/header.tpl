<div id="flash"></div>
<div id="bb-editor-overlay" onclick="hideBBEditor();" style="display:none"></div>
<div id="wysiwyg-editor-overlay" onclick="hideWysiwygEditor();" style="display:none"></div>
<div id="wrap">
    <div id="header" class="clearfix">
        <div id="admin-nav">
            <ul class="nav nav-pills">
                {foreach from=$jojo_admin_nav key=k item=n}{if $k==0}<li id="admintitle"><a href="{$ADMIN}/"><img id="adminlogo" src="{$SITEURL}/images/h45/logo.png" alt="" title="{$OPTIONS.sitetitle} Admin" /></a></li>
                {else}<li class="{if $n.subnav}dropdown{/if}{if $n.selected} active{/if}">
                    <a {if !$n.subnav}href="{$n.url}"{else}class="dropdown-toggle" data-toggle="dropdown" href="" data-target="#"{/if} id="_{$k}" title="{$n.title}"{if $n.title=='Logout'} class="btn btn-default"{/if}>{$n.label}{if $n.subnav}<b class="caret"></b>{/if}</a>{if $n.subnav} 
                    <ul class="dropdown-menu" id="adminsubnav_{$k}">
                        {foreach from=$n.subnav item=s}<li{if $s.selected} class="active"{/if}><a href="{$s.url}" title="{$s.title}">{$s.label}</a></li>{if $s.subnav}
                        {foreach from=$s.subnav item=t}<li{if $t.selected} class="active"{/if}><a href="{$t.url}" title="{$t.title}">&gt; {$t.label}</a></li>
                        {/foreach}
                        {/if}
                        {/foreach}
                    </ul>
                {/if}</li>
                {/if}{/foreach}
                <li><a href="{$SITEURL}/" title="Homepage" target="_blank" class="btn btn-default">Open Site</a></li>
            </ul>
        </div>
    </div>

    <h1 id="h1">{if $displayvalue}{$displayvalue}{elseif $title}{$title}{else}Admin{/if}</h1>
    {if $content}{$content}{/if}
    <div id="container">