$(document).ready(function() {
    $("#search_name").on('keyup', function () {

        var name = ".elements[name='" +  $(this).val() + "']";
        $(".elements.selected").removeClass("selected");
        $(name).addClass("selected");

        active = $(name);

        console.log(active);
       // active = d3.select(this).classed("selected", true);

        var bbox = active[0].getBBox(),
            bounds = [[bbox.x, bbox.y],[bbox.x + bbox.width, bbox.y + bbox.height]]; //<-- the bounds from getBBox

        var dx = bounds[1][0] - bounds[0][0],
            dy = bounds[1][1] - bounds[0][1],
            x = (bounds[0][0] + bounds[1][0]) / 2,
            y = (bounds[0][1] + bounds[1][1]) / 2,
            scale = Math.max(1, Math.min(8, 0.9 / Math.max(dx / width, dy / height))),
            translate = [width / 2 - scale * x, height / 2 - scale * y];

       /* svg.transition()
            .duration(750)*/

    });
})

var xScale;
var xy_zoom;

function installNode(n) {

    if(n['t'] == null)
        return;

    console.log(n.name)
    var parentLevels = [];
    if (typeof n.parentLevelFrom !== 'undefined') {
        for (i = n.parentLevelFrom; i < n.parentLevelTo; i++)
            parentLevels.push(i);
    } else {
        n.targetLinks.forEach(function (t) {
            if (typeof t.parentLevelFrom !== "undefined") {
                for (i = t.parentLevelFrom; i < t.parentLevelTo; i++)
                    parentLevels.push(i);
            }
        });
    }

    var parentIndex = parentLevels.length > 0 ? Math.floor(d3.min(parentLevels)) : 0;
    var index = 0;
   /* var nodeH = 12;
    var nodeW = 2;
    if(n.type == "event") {
        index = 5;*/
        nodeH = slotHeight;
        nodeW = 20;
    //}
    //console.log(n.type);
    var h = Math.max(n.sourceLinks.length, n.targetLinks.length, 1);

    if (n.type == 'birth')  {
        var dt = n.sourceLinks[0].target.t - n.t;

        if(dt < 50) {
            console.log("b", n.name, dt);
            index = parentIndex;
        }
    }
    if (n.type == 'death') {
        var dt =  n.t - n.targetLinks[0].source.t;
        //console.log("d",n.name, dt);
        //if(dt < 50)
            index = parentIndex;
    }
   // console.log("e",n.name, index, n["t"], slots);

   // var minPos = nodeW;
    for(let i = 0;i<n.targetLinks.length;++i){
        if(n.t <  n.targetLinks[i].source.t + nodeW){
         //   console.log(n.name, n.targetLinks[i].source.name);
          //  minPos = Math.max(minPos, nodeW - n.targetLinks[i].source.t + n.t) ;

        }

    }
    //console.log("minO", minPos);
    for(;index < slots.length ;index++) {
       //ole.log(index, slots);
        if(n.t > slots[index]["t"] + spacingH)
        {
           break;
        }
    }
    /*while (typeof slots[index] !== "undefined" && +n["t"] < +slots[index]) {

         index++;
    }*/

    n.targetLinks.forEach(function (l) { // eingehende!!!

      // console.log(l.source.type == "birth");

      if(typeof later[l.source.id] !== 'undefined') {

          later[l.source.id].parentLevelFrom = parentIndex;
          later[l.source.id].parentLevelTo = parentIndex +h;
          insert =  later[l.source.id];
          delete later[l.source.id];
          installNode(insert);
      }
  });

  //  console.log(n.name+ " "+ index + " "+ n["t"] + " " + +slots[index]['t'] );
    // check if node is on same width AND begin/end point

    n.y0 = (nodeH + spacingV) + index * nodeH;
    n.x0 = xScale(n.t);

    let nVIn = 0, nHIn = 0, nHOut = 0, nVOut = 0;

    n.targetLinks.forEach(function (l) { // eingehende!!!
        if( l.source.x1 + spacingH > n.x0 ) {
            //console.log("V:", l.source.name, l.source.t, l.source.x0, l.source.x1, n.x0)
            l.in = "v";
            nVIn++;
        } else {
            //console.log("H:", l.source.name, l.source.t, l.source.x0, l.source.x1, n.x0)
            nHIn++;
            l.in = "h";
        }
    });

    n.nVIn = nVIn;
    n.nHIn = nHIn;

    let iterativeWUnits = minNodeWUnits;

    // start = minW
    do {
        nHOut = 0, nVOut = 0;
        n.sourceLinks.forEach(function (l) { // ausgehende!!!
            if (n.x0 + iterativeWUnits * nodeW + spacingH < xScale(l.target.t)) {
                nHOut++;
                l.out = 'h';
            } else {
                nVOut++;
                l.out = 'v';
            }
        });
        if(nHOut <= iterativeWUnits )
            break;
        iterativeWUnits++;
    }while(1);

    n.nVOut = nVOut;
    n.nHOut = nHOut;

    h = Math.max(1, nHOut, nHIn);

    // reserve h slots, down from index
    for (i = 0; i < h + 1; i++)
        slots[index + i] = n;//  n["t"] + nodeW;

    n.y1 = n.y0 + h * (nodeH);

    if (n.type == 'birth' || n.type == 'death') {
        n.x1 = n.x0 + 5;
    } else {
        n.x1 = n.x0 + nodeWidth(n, true);
    }

    n.sourceLinks.forEach(function (s) { // abgehende!!!
        s.parentLevelFrom = index;
        s.parentLevelTo = index + h;
    });

}

function nodeWidth(d, px = true) {
    let r = Math.max(d.nVIn, d.nVOut, minNodeWUnits);
    if(px)
        r *= elementWidth;
    return r;
}

function flyToPosition(year, duration = 2000) {

    var pos = 0;//clickArea.attributes.width.value / 2;
    //let y = xScale.invert(year);
    let x = xScale(year);
    svg
        .transition()
        .duration(duration)
        .call(
            xy_zoom.transform,
            d3.zoomIdentity.translate(-x+pos, 0)
        ); // updated for d3 v4

}

function createTimeline()
{
   xy_zoom = d3
          .zoom()
          .scaleExtent([0.05, 5])
          .on("zoom", xy_zoomed);

    // Setup:
    const minYear = d3.min(nodes, function (v) {
        return +v["t"];
    });

    nodes.forEach(function (v, i) {
        v["t-seq"] = +v["t"] + Math.abs(minYear);
    });

    const maxYearSeq = d3.max(nodes, function (v) {
        return +v["t-seq"];
    });

  // console.log(d3.extent(nodes, d=>d["t"]), minYear, maxYearSeq , yearLength);

    svg = d3
        .select(".chart")
        .append("svg")
        .attr("preserveAspectRatio", "xMinYMin meet")
        .attr("viewBox", "0 0 "+ ft.width+" " +ft.height)
        .call(xy_zoom);

    xScale = d3
        .scaleLinear()
      /*  .range([minYear* yearLength, (maxYearSeq - minYear) * yearLength])
        .domain(d3.extent(nodes, d=>d["t"]));*/
        .range([0, maxYearSeq * yearLength])
        .domain([0, maxYearSeq]);
        //.domain(d3.extent(nodes, d=>d["t"]));

    var xAxis = d3
        .axisTop(xScale)
        .ticks(100)
        .tickSize(ft.height)
        .tickPadding(8 - ft.height);

    svg
        .append("rect")
        .attr("id", "timeline")
        .attr("class", "zoom0rect")
        .attr("x", 0)
        .attr("y", ft.height - 50)
        .attr("width", ft.width)
        .attr("height", 50)
        .attr("opacity", 0.4)
   // .call(x_zoom);

    const g = svg
        .append("g")
        .attr(
            "transform",
            "translate(" + ft.margin.left + "," + ft.margin.top + ")"
        );

    var gX = svg
        .append("g")
        .attr("class", "axis axis--x")
        .attr("transform", "translate(0," + (ft.height - 1) + ")")
        .call(xAxis);

    var sankey = d3
        .sankey()
        .nodeWidth(4)
        .nodePadding(3)
        .extent([
            [1, 1],
            [
                ft.width - ft.margin.left - 60 - 1,
                ft.height - ft.margin.top - ft.margin.bottom - 6,
            ],
        ]);

    var filteredLinks = [];
    var index = 0;
    links.forEach(function (v) {
        v.id = index++;
        v.value = 1;
        nodes.forEach(function (w) {
            if (v.source === w.id) {
                v.source = w;
            }
            if (v.target === w.id) {
                v.target = w;
            }
        });

        if (typeof v.source === "object" && typeof v.target === "object") {
            filteredLinks.push(v);
        }
    });

    function xy_zoomed() {

        g.attr("transform", d3.event.transform);
        gX.call(xAxis.scale(d3.event.transform.rescaleX(xScale)));

    }

    var data = { nodes: nodes, links: filteredLinks};
    var sankeyNodes = sankey(data).nodes;
    var sankeyLinks = sankey(data).links;

    var color = d3.scaleOrdinal(d3.schemeCategory10);

    function getGradID(d){return "grad-" + d.id;}
    function getLinkID(d) {return d.id;}

    function isVertical(d) { return d.source.x1 > d.target.x0;}

    slots = [];
    later = [];

    sankeyNodes.forEach(function (n) {
        if (n.type == 'birth')
        {
            later[n.id] = n;
        } else {
            installNode(n);
        }
    });

    sankeyNodes.forEach(function (n) {
        var h = Math.max(n.sourceLinks.length, n.targetLinks.length);
        var maxW = 4;
        var c = slotHeight /2 ;
        var r = elementWidth/2  ;
        n.sourceLinks.forEach(function (s) { // abgehende!!!
           // s.parentLevelFrom = index;
           // s.parentLevelTo = index + h;
            if(s.out == "h"){
            //if(s.target.x0 > n.x1)
                s.y0 = n.y0 + c;
                s.x0 = n.x1;
                c+=slotHeight;
            } else { // untereinanderliegende
                s.y0 = n.y1;
                s.x0 = n.x0 + r;
                r+=elementWidth;
            }
        });
        c = slotHeight /2 ;
        r = elementWidth /2 ;
        n.targetLinks.forEach(function (t) {
// eingehende
            if(t.source.x1 < n.x0)
            {
                t.y1 = n.y0 + c;
                t.x1 = n.x0;
                c += slotHeight;
            } else {
                 t.y1 = n.y0;
                t.x1 = n.x0 + r - 0.1;
                r+=elementWidth;
            }
            t.color = color(t.name);
        });

    });

    // horizontale Links

    var lineGenerator = d3.line();
    sankeyLinks.forEach(function(d) {
        lineGenerator.curve(d.curve);
        var points = [[d.x0, d.y0]];
   //     console.log(d);
        if(d.out == "v") {
            points.push([d.x0, d.y0+8]);
        } else {
            points.push([d.x0+20, d.y0]);
        }

       // if(d.y0 < d.y1)
        { // stacking
            points.push(
                [(d.x1 + d.x0)*0.5, (d.y0+d.y1) * 0.5],
            );
        }

        if(d.in == "v") {
            points.push([d.x1, d.y1-8]);
        } else {
            points.push([d.x1-20, d.y1]);
        }
        points.push([d.x1, d.y1]);

        lineGenerator.curve(d3.curveBasis);
        d.lineString = lineGenerator(points);
    });

    var connections = g
        .append("g")
        .attr("transform", "translate(0," + 0 + ")")
        .attr("class", "connections")
        .attr("stroke-opacity", 0.8)

        .selectAll("path")
        .data(sankeyLinks)
        .enter()
        .append("g");

    connections
        .append("path")
        .filter(function(d)
        {
            return (d.source.type == 'event' || !isVertical(d)) ? 1 : 0;
        })
        .attr("fill", "none")
        .attr("class", function(d) {
            return "sankey-links person-" +d.personId;
        })

        .attr("id", function (d) {
            return "link"+ d.id;
        })
       .attr("personId", d => d.personId)
        .attr("d", function (d) {
                return d.lineString;
        })
        .attr("stroke-width", function (d) {
            return Math.min(elementWidth / 2);
        })
      //  .attr("stroke", d=> "url(#" + getGradID(d)+ ")");
     //   .attr("stroke", "url(#hiddenGradient");
      //  .attr("stroke", d=> color(d.name));
        .attr("stroke", function(d) {
            //console.log(maxHeight);
          //  return  color(d.name);
            return (d.source.x1 > d.target.x0 && Math.abs(d.y0 - d.y1) > slotHeight)  ?  "url(#" + getGradID(d)+ ")" : color(d.name);
        } )
        .on("click",function(d) {
            if (d3.event.defaultPrevented) return;
            onLinkClicked(d);
        });


    var defs = g.append("defs");

    var gradient =
        defs.selectAll("linearGradient").data(sankeyLinks, getGradID).enter().append("linearGradient").attr("id", getGradID)



.attr("gradientTransform", "rotate(90)");
    gradient.append("stop")
        .attr('class', 'start')
        .attr("offset", "0%")
        .attr("stop-color", d=> d.color)
        .attr("stop-opacity", 1);

    gradient.append("stop")
        .attr('class', 'start')
        .attr("offset", "25%")
        .attr("stop-color", d=> d.color)
        .attr("stop-opacity", 0.1);

    gradient.append("stop")
        .attr('class', 'start')
        .attr("offset", "75%")
        .attr("stop-color", d=> d.color)
    //    .attr("stop-color", "green")
        .attr("stop-opacity", 0.1);


    gradient.append("stop")
        .attr('class', 'end')
        .attr("offset", "100%")
        .attr("stop-color", d=> d.color)
        .attr("stop-opacity", 1);


    var elements = g
        .selectAll(".elements")
        .data(sankeyNodes)
        .enter()
        .append("g")
      //  .attr("id", d=> d.id)
        .attr("class", function (d) {

            return "elements " + d.type + getClassedPersons(d);
        })
        .attr("transform", function (d) {
            return "translate(" + d.x0 + "," + d.y0 + ")";
        });

    elements.append("rect")

        .filter(function(d)
        {
            return d.type == 'event';
        })
        .attr("name", function (d) {
            return d.name;
        })
        .attr("x", 0)
        .attr("y", 0)

        .attr("id", function (d) {
            return "node"+d.id;
        })
        .attr("width", function(d) {
            return d.x1 - d.x0;//elementWidth * nodeWidth(d)
        })
        .attr("height", function (d) {
            return slotHeight * Math.max(1, d.nHIn, d.nHOut);
        })
        .attr("stroke", function (d) {
            return "#000";
        })

        .on("click",function(d) {
          //  if (d3.event.defaultPrevented) return;
            onNodeClicked(d);
		});

    /*elements
            .filter(function(d)
            {
                return d.type == 'event';
            })
          .append("image")
          .attr("class", "icons")
          .attr("x", 20)
          .attr("y", 20)

          .attr("xlink:href", function (d) {
                  return images[d.id];
          })
          .attr("width", 25)
          .style("display", function (d) {
            {
              return "block";
            }
          });*/

    var nodelabels = elements

              .append("text")
                  .attr("text-anchor", function (d){

                  return (d.type == 'birth') ? "end" : "start";
              }) // middle
              .attr("x", 0)
              .attr("y", function (d) {
                  return 15 ;//d.y0;// (d.type == 'event') ? 15 : d.y0 ;
              })
              .attr("class", "label")
              .attr("dy", 0.1)
              .text(function (d) {
                return d.name ;//+ " v:"+d.nVIn + " h:"+d.nHIn +" vout:"+d.nVOut + " h:"+d.nHOut;
              })
             .call(textwrap,-1, 5);

   // nodelabels.selectAll("text").call(textwrap, 100);
    //textwrap(nodelabels, 100);

          g.selectAll("linklabels")
            .data(sankeyLinks)
            .enter()
       // labels
              .filter(function(d)
              {
                 // console.log(d);
                  return d.source.type == 'event';
              })
            .append("text")
            .attr("x", function (d) {
                return xScale(d.x0) + 20;
            })
            .attr("y", function (d) {
                return d.y0+20;
            })

            .attr("fill", function (d) {
                  return '#884400';
            })
            .text(function (d) {
                return d.name;
            });


          createLinkArrays();





    function createLinkArrays() {

    g
        .append("g")
        .selectAll("path")
        .data(sankeyLinks)
        .enter()
        .append("g")
        .append("path")

        //.attr("fill", "none")
        .attr("class", "arrow")
        .attr("id", function (d) {
            return "link-in-array"+ d.id;
        })
        .attr("d", function (d) {
            if(d.in == "h")
                return "M "+(d.x0-1) + " " +(d.y0-elementWidth/4) +" l " + elementWidth/6 + " " + elementWidth/4  +" l -"+elementWidth/6+" "+elementWidth/4+" z";
            return "M "+(d.x0 -elementWidth/4)+ " " +(d.y0-1) +" l " + elementWidth/2 + " 0 l -" +elementWidth/4+" "+elementWidth/6+" z";
        })
        .attr("fill", function(d) {
            return d.source.type == 'birth' ? "#FFFFFF" : "#FF8800";
        });
    //.attr("fill-opacity", 0.8);


    g
        .append("g")
        .selectAll("path")
        .data(sankeyLinks)
        .enter()
        .append("g")
        .append("path")

        //.attr("fill", "none")
        .attr("class", "arrow")
        .attr("id", function (d) {
            return "link-out-arrow"+ d.id;
        })

        .attr("d", function (d) {
            if(d.out == "h")
                return "M "+(d.x1-1) + " " +(d.y1-elementWidth/4) +" l " + elementWidth/6 + " " + elementWidth/4  +" l -"+elementWidth/6+" "+elementWidth/4+" z";
            return "M "+(d.x1 -elementWidth/4)+ " " +(d.y1-1) +" l " + elementWidth/2 + " 0 l -" +elementWidth/4+" "+elementWidth/6+" z";

        })

        .attr("fill", function(d) {
            return d.color;
        })
        .attr("fill-opacity", 0.8)




    }

    function getClassedPersons(d) {
        let s = "";
        d.targetLinks.forEach(function(t){
            s+=" person-" + t.personId;
        });
        return s;
    }
};


