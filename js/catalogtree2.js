"use strict";
function TreeConfig2(StartGroupId, ActiveGroupId, ActiveElem, LinkPrefix)
{
    return {
        StartGroupId: StartGroupId,
        ActiveGroupId: ActiveGroupId,
        LinkPrefix: LinkPrefix,
        ActiveElem: ActiveElem,
        MultipleClassName: "close",
        SingleClassName: "single",
        GetTreeURL: "/Ajax/get-group-tree",
        DivNameId: "newmenu",
        SubGroupsDivId: "SubGroupsDiv",
        IdFieldName: "ID_group",
        OnClickFunction: null,
        JSONMenu: [],
        getSubGroups: function(ActiveGroup, JSONMenu){
            if(JSONMenu === undefined) { JSONMenu = this.JSONMenu; }
            for(var i=0; i<JSONMenu.length; i++)
            {
                if( JSONMenu[i].children !== undefined && JSONMenu[i].children.length )
                {
                    if( JSONMenu[i].ID_group.toString() === ActiveGroup.toString() )
                    {
                        return JSONMenu[i].children;
                    }
                    else
                    {
                        var SubGroups = this.getSubGroups(ActiveGroup, JSONMenu[i].children);
                        if(SubGroups)
                        {
                            return SubGroups;
                        }
                    }
                }
            }
            return null;
        }
    };
}

function CatalogTree2(LocalMenuConfig)
{
    var instance = this;

    this.JSONfinal = function(str, StatusCode, StatusText)
    {
        if( !checkAndAlertStatus(StatusCode, StatusText) ) { return; }
        LocalMenuConfig.JSONMenu = JSON.parse(str);

        LocalMenuConfig.ActiveElem.appendChild( instance.generateUlNew(LocalMenuConfig.JSONMenu) );

        var MenuTreeDiv = document.createElement('div');
        var SubGroups = LocalMenuConfig.getSubGroups(LocalMenuConfig.ActiveGroupId);
        var UlNode = instance.generateSubGroupsUl(SubGroups);
        if(UlNode)
        {
            MenuTreeDiv.appendChild(UlNode);
            //addClass(MenuTreeDiv, "SubGroupsDiv");
            MenuTreeDiv.style.display = "block";
            var ContainerDiv = document.getElementById(LocalMenuConfig.SubGroupsDivId);
            ContainerDiv.appendChild(MenuTreeDiv);
            ContainerDiv.style.display = "block";
        }
    };

    this.fetchJSONTree = function()
    {
        sendRequest( LocalMenuConfig.GetTreeURL, 
            "POST", 
            JSON.stringify( {Present: 1, ID: LocalMenuConfig.StartGroupId} ), 
            this.JSONfinal,
            this );
    };

    this.generateSubGroupsUl = function(SubGroups)
    {
        if( !SubGroups || !SubGroups.length )
        {
            return null;
        }
        var UlNode = document.createElement('ul');
        SubGroups.forEach( function(item){
            var LiNode = document.createElement('li');
            var AEl =  document.createElement('a');
            AEl.href = "/" + LocalMenuConfig.LinkPrefix + "/" + item.GroupTranslit;
            AEl.text = item.GroupTitle;

            LiNode.appendChild(AEl);
            UlNode.appendChild(LiNode);

        } );
        return UlNode;
    };

    this.generateUlNew = function(Nodes)
    {
        var TreeUl = document.createElement('ul');
        var LiNode;
        var DivEl;
        var AEl;

        for(var i=0; i < Nodes.length; i++ )
        {
            LiNode = document.createElement('li');
            LiNode.id = Nodes[i][LocalMenuConfig.IdFieldName];

            // добавляем отклик на выбор-нажатие элемента
            DivEl = document.createElement('div');
            AEl = document.createElement('a');

            LiNode.appendChild(DivEl);
            AEl.href = "/" + LocalMenuConfig.LinkPrefix + "/" + Nodes[i].GroupTranslit;
            AEl.text = Nodes[i].GroupTitle;

            LiNode.appendChild(AEl);

            if(typeof(Nodes[i].children) !== 'undefined' && Nodes[i].children)
            {
                if( LocalMenuConfig.OnClickFunction )
                {
                    DivEl.onclick = LocalMenuConfig.OnClickFunction;
                }
                DivEl.className = "close";
                LiNode.appendChild( this.generateUlNew(Nodes[i].children) );
                LiNode.className = LocalMenuConfig.MultipleClassName;
            }
            else
            {
                //var Href = AEl.href;
                DivEl.className = "single";
                //DivEl.onclick = function(){ window.location = Href; };
                LiNode.className = LocalMenuConfig.SingleClassName;
            }

            TreeUl.appendChild(LiNode);
        }

        return TreeUl;
    };

}
