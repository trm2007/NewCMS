"use strict";
function TreeConfig(StartGroupId, ActiveGroupId, LinkPrefix)
{
    return {
        StartGroupId: StartGroupId,
        ActiveGroupId: ActiveGroupId,
        LinkPrefix: LinkPrefix,
        CloseClassName: "close",
        OpenClassName: "open",
        SingleClassName: "single",
        GetTreeURL: "/Ajax/get-group-tree",
        DivNameId: "catalog-menu",
        IdFieldName: "ID_group"
    };
}

function CatalogTree(LocalMenuConfig)
{
    var instance = this;

    this.JSONfinal = function(str, StatusCode, StatusText)
    {
        if( !checkAndAlertStatus(StatusCode, StatusText) ) { return; }
        var JSONMenu = JSON.parse(str);
        var AJAXDiv = document.getElementById(LocalMenuConfig.DivNameId);
        
        AJAXDiv.innerHTML = "";
        AJAXDiv.appendChild( instance.generateUlNew(JSONMenu) );
        instance.openFolder(LocalMenuConfig.ActiveGroupId);
    };

    this.clickFolder = function(e)
    {
        switchClass(e.target.parentNode, LocalMenuConfig.CloseClassName, LocalMenuConfig.OpenClassName);
    };

    this.openFolderParents = function(e)
    {
        var AJAXDiv = document.getElementById(LocalMenuConfig.DivNameId);
        var Elem = e.parentNode;

        while(Elem !== AJAXDiv)
        {
            if( Elem.tagName === 'LI' && haveClass(Elem, LocalMenuConfig.CloseClassName) )
            {
                removeClass(Elem, LocalMenuConfig.CloseClassName);
                addClass(Elem, LocalMenuConfig.OpenClassName);
            }
            Elem = Elem.parentNode;
        }
    };

    this.openFolder = function(id)
    {
        var AJAXDiv = document.getElementById(LocalMenuConfig.DivNameId);
        var LiNodes = AJAXDiv.getElementsByTagName('li');
        var Val;
        for(var i = 0; i < LiNodes.length; i++)
        {
            Val = Number(LiNodes[i].id);
            if( Val === id )
            {
                if( haveClass(LiNodes[i], LocalMenuConfig.CloseClassName) )
                {
                    removeClass(LiNodes[i], LocalMenuConfig.CloseClassName);
                    addClass(LiNodes[i], LocalMenuConfig.OpenClassName);
                }
                addClass(LiNodes[i].childNodes[1], "active");
                this.openFolderParents(LiNodes[i]);
                return;
            }
        }
    };

    this.closeAllTree = function()
    {
        var AJAXDiv = document.getElementById(LocalMenuConfig.DivNameId);
        var LiNodes = AJAXDiv.getElementsByTagName('li');
        for(var i = 0; i < LiNodes.length; i++)
        {
            if( LiNodes[i].tagName === 'LI' && haveClass(LiNodes[i], LocalMenuConfig.OpenClassName) )
            {
                removeClass(LiNodes[i], "active");
                removeClass(LiNodes[i], LocalMenuConfig.OpenClassName);
                addClass(LiNodes[i], LocalMenuConfig.CloseClassName);
            }
        }
        return;
    };

    this.generateUlNew = function(Nodes)
    {
        var TreeUl = document.createElement('ul');
        var LiNode;
        var FolderEl;
        var DivEl;
        var AEl;

        for(var i=0; i < Nodes.length; i++ )
        {
            LiNode = document.createElement('li');
            LiNode.id = Nodes[i][LocalMenuConfig.IdFieldName];

            FolderEl = document.createElement("div");
            FolderEl.className = "hiticon";

            LiNode.appendChild(FolderEl);

            // добавляем отклик на выбор-нажатие элемента
            DivEl = document.createElement('div');
            AEl = document.createElement('a');

            AEl.href = "/" + LocalMenuConfig.LinkPrefix + "/" + Nodes[i].GroupTranslit;
            AEl.text = Nodes[i].GroupTitle;
            DivEl.appendChild(AEl);
            LiNode.appendChild(DivEl);

            if(typeof(Nodes[i].children) !== 'undefined' && Nodes[i].children)
            {
                LiNode.appendChild( this.generateUlNew(Nodes[i].children) );
                FolderEl.onclick = this.clickFolder;
                if( Nodes[i][LocalMenuConfig.IdFieldName] == LocalMenuConfig.StartGroupId )
                {
                    LiNode.className = LocalMenuConfig.OpenClassName;
                }
                else
                {
                    LiNode.className = LocalMenuConfig.CloseClassName;
                }
            }
            else
            {
                LiNode.className = LocalMenuConfig.SingleClassName;
            }

            TreeUl.appendChild(LiNode);
        }

        return TreeUl;
    };

    this.fetchJSONTree = function()
    {
        sendRequest( LocalMenuConfig.GetTreeURL, 
            "POST", 
            JSON.stringify( {Present: 1, ID: LocalMenuConfig.StartGroupId} ), 
            this.JSONfinal,
            this );
    };
}
