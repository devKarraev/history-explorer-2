$(document).ready(function() {
  var element_names = ft.nodes.map(function (v) {
    return v.name;
    });

  $("#search_name").on('keyup', function () {

      var s = $(this).val();

      var name = ".elements[name='" + s + "']";

      $(".elements.selected").removeClass("selected");
      $(name).addClass("selected");

      if ($(name).length > 0) {

          var selectedNode = d3.selectAll(name);
          //node = $(name);
          bounds = [
              [[], []],
              [[], []],
          ];

          console.log(selectedNode);
          selectedNode.each(function (d) {
              var bbox = d3
                  .select("#node" + d.id)
                  .node()
                  .getBBox();
              console.log(bbox);
              bounds[0][0].push(bbox.x);
              bounds[0][1].push(bbox.y);
              bounds[1][0].push(bbox.x + bbox.width);
              bounds[1][1].push(bbox.y + bbox.height);
          });
          bounds[0][0] = d3.min(bounds[0][0]);
          bounds[0][1] = d3.min(bounds[0][1]);
          bounds[1][0] = d3.max(bounds[1][0]);
          bounds[1][1] = d3.max(bounds[1][1]);
          console.log(bounds);
          /*var bbox = selectedNode.node().getBBox(),
            bounds = [
              [bbox.x, bbox.y],
              [bbox.x + bbox.width, bbox.y + bbox.height],
            ];*/

          var dx = bounds[1][0] - bounds[0][0],
              dy = bounds[1][1] - bounds[0][1],
              x = (bounds[0][0] + bounds[1][0]) / 2,
              y = (bounds[0][1] + bounds[1][1]) / 2,
              /*scale = Math.max(
                1,
                Math.min(8, 0.9 / Math.max(dx / ft.width, dy / ft.height))
              ),*/
              scale = 0.9 / Math.max(dx / ft.width, dy / ft.height),
              translate = [ft.width / 2 - scale * x, ft.height / 2 - scale * y];

          svg
              .transition()
              .duration(2000)
              // .call(zoom.translate(translate).scale(scale).event); // not in d3 v4
              .call(
                  xy_zoom.transform,
                  d3.zoomIdentity.translate(translate[0], translate[1]).scale(scale)
              ); // updated for d3 v4

      } else {
         // console.log("s", s);
          /*
            - create list of options -> element_names
            - check if entry matches start of any in list
            - make list of those matches -> results
            - populate drop down with that list
          */
          var results = [];
          element_names.forEach(function (v) {
              if (s.toUpperCase() == v.slice(0, s.length).toUpperCase()) {
                  results.push(v);
              }
          });
        }
  });
});

function toggleBooks(){

    var active = $("#show_books").hasClass("disabled") ? 1 : 0;
    if(active) {
        $("#show_books").removeClass("disabled");
    } else {
        $("#show_books").addClass("disabled");
    }

    d3.selectAll("svg .b")
        .classed("hidden", function () {
            return active ? false : true;
        });
   /* book_elements.transition()           // apply a transition
        .duration(500)         // apply it over 500 milliseconds
        .style("opacity", function(f)
            {return active > 0 ? 1 : 0 });*/


}
function toggleFolks(){
    var active = $("#show_folks").hasClass("disabled") ? 1 : 0;
    if(active) {
        $("#show_folks").removeClass("disabled");
    } else {
        $("#show_folks").addClass("disabled");
    }

    d3.selectAll("svg .folk")
        .classed("hidden", function () {
            return active ? false : true;
        });

}

            /*var autocompleteUrl = $(this).data('autocomplete-url');

            $.ajax({
                url: autocompleteUrl + '?query=' +$(this).val()
            }).then(function(data) {
                if(data.name) {
                    $("#search_name").val(data.name);
                } else {

                }
            });*/



var connections = {};
var elements = {};
var labels = {}, icons ={};
var xy_zoom;
var book_elements ={}, bookLabels ={}, gBooks ={};
var bookroll ={}
var bookrollStatic ={}
function onNodeClicked(node){

    refreshPerson(node.id);

    $(".elements.selected").removeClass("selected");
    $(".sankey-links.selected").removeClass("selected");
    $('#node' + node.id + '.elements').addClass("selected");

}

function isConnected(a, b) {
    return ft.linkedByIndex[`${a.id},${b.id}`] || ft.linkedByIndex[`${b.id},${a.id}`] || a.id === b.id;
}

function transition(d) {


    elements.transition()           // apply a transition
            .duration(500)         // apply it over 100 milliseconds
            .attr('y', function(f) {return f.y0});
    labels.transition()           // apply a transition
        .duration(500)         // apply it over 100 milliseconds
        .attr('y', function(f) {return f.y0});

    var counter = 1;
    var direction = 1;
    d.targetLinks.forEach(function (t) {
        var y = d.y0 - (slotHeight + spacingV) * counter * direction;
        t.y0 = y + slotHeight/2;
        console.log(t);
        d3.select("#node"+ t.source.id)
        .transition()           // apply a transition
            .duration(1000)         // apply it over 100 milliseconds
            .attr('y', y);

        d3.select("#label"+ t.source.id)
            .transition()           // apply a transitionboo
            .duration(1000)         // apply it over 100 milliseconds
            .attr('y', y - slotHeight/2);

        counter++;

    });
    counter = 1;
    direction = -1;
    d.sourceLinks.forEach(function (t) {
        var y = d.y0 - (slotHeight + spacingV) * counter * direction;
        t.y1 = y + slotHeight/2;
        console.log(t);
        d3.select("#node"+ t.target.id)
            .transition()           // apply a transition
            .duration(1000)         // apply it over 100 milliseconds
            .attr('y', y);

        d3.select("#label"+ t.target.id)
            .transition()           // apply a transition
            .duration(1000)         // apply it over 100 milliseconds
            .attr('y', y - slotHeight/2);
        counter++;
    });
}

function fade(opacity, d) {
    //return d => {

    elements.style('stroke-opacity', function (o) {

        const thisOpacity = (isConnected(d, o)) ? 1 : opacity ;
        this.setAttribute('fill-opacity', thisOpacity);
        return thisOpacity;
    });

   labels.style('opacity', function (o) {

        const thisOpacity = (isConnected(d, o)) ? 1 : opacity ;
        this.setAttribute('opacity', thisOpacity);
        return thisOpacity;
    });

    connections.style('stroke-opacity', o => (o.source === d || o.target === d ? 1 : opacity));

   // };
}

function installNode(n) {

    var parentLevels = [];

    if(typeof n.parentLevel !== 'undefined') {
        parentLevels.push(n.parentLevel);
    } else {
        n.targetLinks.forEach(function (t) {
            if ((typeof t.parentLevel !== "undefined") && (typeof t.source.added !== "undefined")) {
                parentLevels.push(t.parentLevel);
            }
        });
    }
    parentLevels.sort();
    // search slot near to parent slot
    if(parentLevels.length == 0 && n.level != 0) {
        ft.parentless[n.id] = n;
    } else {

        var parentIndex = parentLevels.length > 0 ? Math.floor(d3.median(parentLevels)) : 0;
        var distance = 1;
        var direction = -1;
        var index = parentIndex;
        while (
            typeof slots[index] !== "undefined" &&
                n["born"] < slots[index]
            ) {
            index = parentIndex + direction * distance;
            if (index < 0) {
                index = parentIndex + distance;
            }
            if (direction === 1) {
                distance++;
            }
            direction *= -1;
        }

        if(n.gender === 'folk') {
            slots[index] = n["born"] + 40;
            n.y0 = (index + 0.25) * (slotHeight + spacingV) ;
        } else {
            slots[index] = n["died"];
            n.y0 = index * (slotHeight + spacingV);
        }

        n.y1 = n.y0 + slotHeight;
        n.x0 = n["born"];
        n.x1 = Math.max(n.x0+1, n["died"]);

        n.added = 1;

        // apply node positions to links
        n.sourceLinks.forEach(function (s) {
            s.y0 = n.y0 + (n.y1 - n.y0) / 2;
            s.parentLevel = index;

            if(typeof ft.parentless[s.target.id] !== 'undefined')
            {
                s.target.parentLevel = index;
                insert =  ft.parentless[s.target.id];
                delete ft.parentless[s.target.id];
                installNode(insert);
            }

        });
        n.targetLinks.forEach(function (t) {
            t.y1 = n.y0 + (n.y1 - n.y0) / 2;
            t.x0 = t.x1 = n.x0;

            if(typeof ft.parentless[t.source.id] !== 'undefined')
            {
                t.source.parentLevel = index;
                installNode(ft.parentless[t.source.id]);
                delete ft.parentless[t.source.id];
            }

        });
    }
}


function createTree() {
      ft.parentless = {};
      ft.linkedByIndex = {};
      var x_zoom = d3.zoom().on("zoom", x_zoomed);

      xy_zoom = d3.zoom().scaleExtent([0.11, 2.5]).on("zoom", xy_zoomed);

    // Setup:
    const minYear = d3.min(ft.nodes, function (v) {
        return +v["born"];
    });
    const maxYear = d3.min(ft.nodes, function (v) {
        return +v["died"];
    });

    ft.nodes.forEach(function (v, i) {
        v["born"] = +v["born"];// + Math.abs(minYear);
        v["died"] = +v["died"];// + Math.abs(minYear);
    });

    ft.books.forEach(function (v, i) {
        v["born"] = +v["born"];// + Math.abs(minYear);
        v["died"] = +v["died"];// + Math.abs(minYear);
    });

    const maxYearSeq = d3.max(ft.nodes, function (v) {
        return +v["died"];
    });
    console.log("x", ft.width);

   svg = d3
      .select(".chart")
      .append("svg")
     // .attr("preserveAspectRatio", "xMinYMin meet")
      .attr("viewBox", "0 0 "+ ft.width+" " +ft.height )
      .call(xy_zoom)
       // .attr("height", ft.height)
  //     .attr("width", ft.width)
      /*  .attr("width", canvas.width - ft.margin.left - ft.margin.right)
        .attr("height", canvas.height - ft.margin.top - ft.margin.bottom);*/

    const g = svg
        .append("g")
        .attr(
            "transform",
            "translate(" + ft.margin.left + "," + ft.margin.top + ")"
        )


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
    ft.links.forEach(function (v) {
       // console.log(v.source.index);
        v.id = index++;
        v.value = 1;
        ft.nodes.forEach(function (w) {
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

        ft.linkedByIndex[`${v.source.id},${v.target.id}`] = 1;

    });
    var data = { nodes: ft.nodes, links: filteredLinks};
    var sankeyNodes = sankey(data).nodes;
    var sankeyLinks = sankey(data).links;

    slots = [];
    bookSlots = [];

    // manipulate data
    sankeyNodes.forEach(function (n) {
        installNode(n)
    });

    ft.books.forEach(function (n) {
        var index = 0;
        while (
            typeof bookSlots[index] !== "undefined" &&
                n["born"] < bookSlots[index] + 20
            ) {
          index++;

        };
        bookSlots[index] = n["died"];
        n.y0 = ft.height - 100 - ft.bookHeight * 1.4 *(index +1);
        n.y1 = n.y0 + ft.bookHeight;
        n.x0 = +n["born"];
        n.x1 = +n["died"];
    });

    const yearLength_0 = 3;
    const yearLength_1 = 3.0;
    var ranges = [];
    var yearDomains = [];

    var firstKey = Object.keys(yearLengths)[0];
    var firstVal = yearLengths[firstKey];
    delete yearLengths[firstKey];
    yearLengths[minYear] = firstVal;
    console.log(ranges);

    const orderedYearLengths = {};
    Object.keys(yearLengths).sort().reverse().forEach(function(key) {
        orderedYearLengths[key] = yearLengths[key];
    });

    var rangeSum = 0;
    var lastYear = minYear;
    for (const [key, value] of Object.entries(orderedYearLengths)) {
        if(key < maxYearSeq) {
            console.log(minYear, lastYear, key);
            yearDomains.push(key);
            ranges.push(rangeSum);
           // lastYear = k;
        }
    };
    console.log(ranges);
    console.log(yearDomains,  0, (-minYear-2000) * yearLength_0,
        (- minYear -2000) * yearLength_0 + (maxYearSeq - (- 2000)) * yearLength_1);

    var xScale = d3
        .scaleLinear()
        .range([0, (maxYearSeq - minYear) * yearLength])
        .domain([minYear, maxYearSeq]);
        /*.range([0, (-2000 - minYear) * yearLength_0,
            (-2000 - minYear) * yearLength_0 + (maxYearSeq - (- 2000)) * yearLength_1])
        .domain([minYear, -2000, maxYearSeq])
        .clamp(true);*/
  var xAxis = d3
    .axisTop(xScale)
    .ticks(100)
    .tickSize(ft.height)
    .tickPadding(8 - ft.height);

  var targetHeight = ft.height;// d3.select(".chart")._parents[0].clientHeight;
  console.log("targetHeight", targetHeight);
  var targetWidth = d3.select(".chart")._parents[0].clientWidth;
  /*Zoom =*/ svg
    .append("rect")
    .attr("id", "timeline")
    .attr("class", "zoom0rect")
    .attr("x", 0)
    .attr("y", targetHeight - 50)
    .attr("width", targetWidth)
    .attr("height", 50)
    .attr("opacity", 0.4)
    .call(x_zoom);

  var gX = svg
    .append("g")
    .attr("class", "axis axis--x")
    .attr("transform", "translate(0," + (targetHeight - 1) + ")")
        .call(xAxis);


  function getGradID(d) {
    return "grad-" + d.id;
  }
  function getLinkColor(d) {
    return d.target.gender == "folk" || d.source.gender == "folk"
      ? "#FFAA00"
      : d.source.gender == "w"
      ? "#FF0066"
      : d.source.gender == "m"
      ? "#0066FF"
      : "#00FF00";
  }
  function getNodeColor(d) {
    return d.gender == "folk" || d.gender == "folk"
      ? "#DDDD00"
      : d.gender == "w"
      ? "#FF6666"
      : d.gender == "m"
      ? "#AAAAAA"
      : "#00FF00";
  }

  var defs = g.append("defs");
  var gradient = defs
    .selectAll("linearGradient")
    .data(sankeyNodes, getGradID)
    .enter()
    .append("linearGradient")
    .attr("id", getGradID);
  //.attr("gradientTransform", "rotate(90)");
  gradient
    .append("stop")
    .attr("class", "start")
    .attr("offset", "0%")
    .attr("stop-color", (d) => getNodeColor(d))
    .attr("stop-opacity", (d) => (d.fuzzyBegin == "c" ? 0.1 : 1.0));

  gradient
    .append("stop")
    .attr("class", "start")
    .attr("offset", "15%")
    .attr("stop-color", (d) => getNodeColor(d))
    .attr("stop-opacity", (d) => (d.gender == "folk" ? 0.2 : 1.0));

  gradient
    .append("stop")
    .attr("class", "start")
    .attr("offset", "85%")
    .attr("stop-color", (d) => getNodeColor(d))
    .attr("stop-opacity", (d) => (d.gender == "folk" ? 0.2 : 1.0));

  gradient
    .append("stop")
    .attr("class", "end")
    .attr("offset", "100%")
    .attr("stop-color", (d) => getNodeColor(d))
    .attr("stop-opacity", (d) => (d.fuzzyEnd == "c" ? 0.1 : 1.0));


  const maxX = d3.max(sankeyNodes, function (v) {
    return +v["x1"];
  });
  const maxY = d3.max(sankeyNodes, function (v) {
    return +v["y1"];
  });

  var background = g
    .append("rect")
    .attr("height", maxY)
    .attr("width", xScale(maxYearSeq))
    .attr("opacity", 0)
    .on("click", function (d) {
      fade(1, this);
    });



   createLinks();
   createNodes();
   createBookRolls();

   function createLinks() {
       connections = g
           .append("g")
           .attr("transform", "translate(0," + 0 + ")")
           //.attr("class", "connections")
           .attr("fill", "none")
           .attr("stroke-opacity", 0.4)
           .selectAll("path")
           .data(sankeyLinks)
           .enter()
           .append("path")
           .attr("class", function(d){
               var g = d.target.gender;
               if(g !== d.source.gender) g+=" " +d.source.gender;
               return "sankey-links "+ g;
           })
           .attr("id", function (d) {
               return "node-connection" + d.id;
           })
           .attr("d", function (d) {
               return d3
                   .sankeyLinkHorizontal()
                   .source(function () {
                       return [xScale(d.x0) - 60, d.y0];
                   })
                   .target(function () {
                       return [xScale(d.x0), d.y1];
                   })();
           })

           .attr("stroke-width", function (d) {
               return Math.min(elementWidth / 4);
           })
           .attr("stroke", function (d) {

               return getLinkColor(d);
               //  return "url(#" + getGradID(d)+ ")" ;//return (d.target.gender == "folk" || d.source.gender == "folk") ? "#FFAA00" : d.source.gender == "w" ? "#FF0066" : d.source.gender == "m" ? "#0066FF" : "#00FF00";
           });
   }
  function createNodes() {

      elements = g
          .selectAll("rect")
          .data(sankeyNodes)
          .enter()
          .append("rect")
          .attr("name", function (d) {
              return d.name;
          })
          .attr("x", function (d) {
              return xScale(d.x0);
          })
          .attr("id", function (d) {
              return "node" + d.id;
          })
          .attr("width", function (d) {

              return xScale(d.x1) - xScale(d.x0);
          })
          .attr("y", function (d) {
              return d.y0;
          })
          .attr("height", function (d) {
              return d.y1 - d.y0;
          })
          .attr("class", function (d) {
              /*if(d.gender === "folk" )
                  return "elements f";*/
              return "elements " + d.gender;
          })

          .style("fill", function (d) {
              return "url(#" + getGradID(d)+ ")" ;
          })
          .on("click", function(d) {
                  onNodeClicked(d);
                  fade(0.20, d);
                  transition(d);

              }
          );

      icons = g
          .selectAll("image")
          .data(sankeyNodes)
          .enter()
          .append("image")
          .attr("class", "icons")
          .attr("x", function (d) {
              return xScale(d.x0) - 12.5;
          })
          .attr("y", function (d) {
              return d.y0 + slotHeight / 6;
          })
          .attr("xlink:href", function (d) {
              return ft.images[d.id];
          })
          .attr("width", 25)
          .style("display", function (d) {
              if (xScale(d.x1) - xScale(d.x0) < 15) {
                  return "none";
              } else {
                  return "block";
              }
          });

      labels = g
          .selectAll("text")
          .data(sankeyNodes)
          .enter()
          .append("text")
          .attr("class", function (d) {
              return "label " + d.gender;
          })
          .attr("x", function (d) {
              return xScale(d.x0) + 20;
          })
          .attr("y", function (d) {
              return d.y0 + (d.y1 - d.y0) / 2;
          })
          .attr("id", function (d) {
              return "label"+d.id;
          })
          .text(function (d) {
              return d.name ;//+ " " + d.born + " " + d.died;
          });

      labels
          .each(function (d) {
              d.label_length = d3
                  .select(this)
                  .node()
                  .getBoundingClientRect().width;
          })
          .style("display", function (d) {
              if (xScale(d.x1) - xScale(d.x0) < d.label_length + 40) {
                  return "none";
              } else {
                  return "block";
              }
          });
  }

function createBookRolls() {

    let defs = g.append("defs");
    let gradient = defs
        .append("linearGradient")
        .attr("id", "grad-bookroll-outside");

    gradient
        .append("stop")
        .attr("class", "start")
        .attr("offset", "0%")
        .attr("stop-color", "#FFCC88");
    gradient
        .append("stop")
        .attr("class", "end")
        .attr("offset", "100%")
        .attr("stop-color", "#885500");

    let gradientInside = defs
        .append("linearGradient")
        .attr("id", "grad-bookroll-inside");

    gradientInside
        .append("stop")
        .attr("class", "start")
        .attr("offset", "0%")
        .attr("stop-color", "#885500");
    gradientInside
        .append("stop")
        .attr("class", "end")
        .attr("offset", "100%")
        .attr("stop-color", "#C39044");

    gBooks = svg
        .append("g")
        .attr(
            "transform",
            "translate(" + ft.margin.left + "," + ft.margin.top + ")"
        );

    let e = ft.bookHeight / 3;
    let d = ft.bookHeight / 3 * 2;
    //let h= 10;

   /* bookrollR = gBooks
        .selectAll(".roll_right")
        .data(ft.books)
        .enter()
        .append("g");*/

    bookroll = gBooks
        .selectAll("g")
        .data(ft.books)
        .enter()
        .append("g")
        .attr("class", "elements b");


    bookroll
        .append("rect")
        //.attr("class", "bookroll right")
        .attr("x", function (f) {
            return xScale(f.x1) -  d;
        })
        .attr("width", function (f) {
            return 2 * d;
        })
        .attr("y",  function (d) {
            return d.y0 - e * 1.5;
        })
        .attr("height", function (d) {
            return d.y1 - d.y0 + e *1.5;
        })
        .attr("rx", d)
        .attr("ry", e)
        .style("fill", function (d) {
            return "url(#grad-bookroll-outside)" ;}
        );

    bookroll
        .append("ellipse")
        .attr("cx", (f,i) => { return xScale(f.x1) ; })
        .attr("cy", (f,i) => { return f.y0 - e; })
        .attr("rx", (f,i) => { return d; })
        .attr("ry", (f,i) => { return e; })
        .style("fill", function (d) {
            return "url(#grad-bookroll-inside)" ;
        });

    bookroll
        .append("rect")
        .attr("name", function (d) {
            return d.name;
        })
        .attr("x", function (d) {
            return xScale(d.x0);
        })
        .attr("width", function (d) {
            return xScale(d.x1) - xScale(d.x0);
        })
        .attr("y",  function (d) {
            return d.y0 ;
        })
        .attr("height", function (d) {
            return d.y1 - d.y0;
        })
      /*  .attr("class", function (d) {
            return "elements b";
        });*/


    bookroll
        .append("rect")
      //  .attr("class", "elements b left")
        .attr("x", function (f) {
            return xScale(f.x0) -  d;
        })
        .attr("width", function (f) {
            return 2 * d;
        })
        .attr("y",  function (d) {
            return d.y0 ;
        })
        .attr("height", function (d) {
            return d.y1 - d.y0 + e;
        })
        .attr("rx", d)
        .attr("ry", e)
        .style("fill", function (d) {
            return "url(#grad-bookroll-outside)" ;
        });


    bookroll
        .append("ellipse")
        .attr("cx", (f,i) => { return xScale(f.x0); })
        .attr("cy", (f,i) => { return f.y0 + e; })
        .attr("rx", (f,i) => { return d; })
        .attr("ry", (f,i) => { return e; })
        .style("fill", function (d) {
            return "url(#grad-bookroll-inside)";
        });

    bookLabels = gBooks
        .selectAll("text")
        .data(ft.books)
        .enter()
 //   bookLabels = rollLeft
        .append("text")
        .attr("class", "label b")
        .attr("x", function (d) {
            return xScale(d.x0) + 40;
        })
        .attr("y", function (d) {
            return d.y0 + (d.y1 - d.y0) / 2 +5;
        })
        .text(function (d) {
            return d.name ;
        });

    bookLabels
        .each(function (d) {
            d.label_length = d3
                .select(this)
                .node()
                .getBoundingClientRect().width;
        })
        .style("display", function (d) {
            /* if (xScale(d.x1) - xScale(d.x0) < d.label_length + 40) {
                 return "none";
             } else */{
                return "block";
            }
        });
};

        function x_zoomed() {

          // g.attr("transform", d3.event.transform);
          const transform = d3.event.transform;
          const xNewScale = transform.rescaleX(xScale);
     //     d3.zoomIdentity.translate(-transform.x);
          gX.call(xAxis.scale(d3.event.transform.rescaleX(xScale)));
          elements
            .attr("x", function (d) {
                return xNewScale(d.x0);
            })
            .attr("width", function (d) {
                return xNewScale(d.x1) - xNewScale(d.x0);
            });

          labels
            .attr("x", function (d) {
              return xNewScale(d.x0) + 20;
            })
            .style("display", function (d) {
              if (xNewScale(d.x1) - xNewScale(d.x0) < d.label_length + 40) {
                return "none";
              } else {
                return "block";
              }
            });

          icons
            .attr("x", function (d) {
              return xNewScale(d.x0) - 12.5;
            })
            .style("display", function (d) {
              if (xNewScale(d.x1) - xNewScale(d.x0) < 15) {
                return "none";
              } else {
                return "block";
              }
            });

          connections.data(sankeyLinks).attr("d", function (d) {
            return d3
              .sankeyLinkHorizontal()
              .source(function () {
                return [xNewScale(d.x0) - 40, d.y0];
              })
              .target(function () {
                return [xNewScale(d.x0), d.y1];
              })();
          });
        }



    function xy_zoomed() {

        g.attr("transform", d3.event.transform);
        gX.call(xAxis.scale(d3.event.transform.rescaleX(xScale)));
        const xNewScale = d3.event.transform.rescaleX(xScale);

        bookroll.attr("transform", "translate("+ d3.event.transform.x + ",0)scale(" +d3.event.transform.k +", 1)");


        bookLabels
            .attr("x", function (d) {
                return xNewScale(d.x0) + 20;
            })
            .style("display", function (d) {
                if (xNewScale(d.x1) - xNewScale(d.x0) < d.label_length + 40) {
                    return "none";
                } else {
                    return "block";
                }
            });

    }
};
