              <div align="center"><div id="thegraph" style="width: 750px; height: 400px;"></div></div>
              <script id=source language=javascript type="text/javascript">
                {$am}
                {$tm}
                {$load1}
                {$load5}
                {$load15}
                {literal}
                  var options = {
                      yaxis: {tickFormatter: function (v, axis) {
                              return v.toFixed(axis.tickDecimals) +" Load"
                          }},
                      y2axis: {tickFormatter: function (v, axis) {
                              return v.toFixed(axis.tickDecimals) +" MB"
                          }},
                      xaxis: { mode: "time", timeformat: "%H:%M:%S UTC" },
                      selection: { mode: "x" },
                      grid: { hoverable: true }
                  };

                  function drawIt() {
                      $.plot($("#thegraph"), [
                      {
                          data: tm,
                          label: "Total Memory",
                          color: "#cccccc",
                          yaxis: 2,
                          lines: { show: true, fill: true, fillColor: "rgba(128, 128, 128, 0.15)" },
                          points: { show: true, fill: false },
                      },

                      {
                          data: am,
                          label: "Available Memory",
                          color: "#be95ce",
                          yaxis: 2,
                          lines: { show: true, fill: true, fillColor: "rgba(157, 89, 200, 0.15)" },
                          points: { show: true, fill: false },
                      },

                      {
                          data: load1,
                          label: "1 Minute Load",
                          color: "#6a99e8",
                          lines: { show: true, fill: false },
                          points: { show: true, fill: false },
                      },

                      {
                          data: load5,
                          label: "5 Minute Load",
                          color: "#118b3f",
                          lines: { show: true, fill: false },
                          points: { show: true, fill: false },
                      },

                      {
                          data: load15,
                          label: "15 Minute Load",
                          color: "orange",
                          lines: { show: true, fill: false },
                          points: { show: true, fill: false },
                      }], options);
                  };

                  drawIt();

                  $("#thegraph").bind("plotselected", function (event, ranges) {
                      var zoom = $("#zoom").attr("checked");
                      $.plot($("#thegraph"), [
                      {
                          data: tm,
                          label: "Total Memory",
                          color: "#cccccc",
                          yaxis: 2,
                          lines: { show: true, fill: true, fillColor: "rgba(128, 128, 128, 0.15)" },
                          points: { show: true, fill: false },
                      },

                      {
                          data: am,
                          label: "Available Memory",
                          color: "#be95ce",
                          yaxis: 2,
                          lines: { show: true, fill: true, fillColor: "rgba(157, 89, 200, 0.15)" },
                          points: { show: true, fill: false },
                      },

                      {
                          data: load1,
                          label: "1 Minute Load",
                          color: "#6a99e8",
                          lines: { show: true, fill: false },
                          points: { show: true, fill: false },
                      },

                      {
                          data: load5,
                          label: "5 Minute Load",
                          color: "#118b3f",
                          lines: { show: true, fill: false },
                          points: { show: true, fill: false },
                      },

                      {
                          data: load15,
                          label: "15 Minute Load",
                          color: "orange",
                          lines: { show: true, fill: false },
                          points: { show: true, fill: false },
                      }],

                      $.extend(true, {}, options, {
                          xaxis: { min: ranges.xaxis.from, max: ranges.xaxis.to }
                      }));
                      displayOut();
                  });

                  function displayOut() {
                      $("<div id='zoom'><a href=\"#\" onclick=\"javascript:zoomClick(); return false;\">Zoom out</a></div>").css( {
                          position: "absolute",
                          display: "none",
                          top: 15,
                          left: 55,
                          border: "1px solid #fdd",
                          padding: "2px",
                          "background-color": "#fee",
                          opacity: 0.80
                      }).appendTo("#thegraph").show();
                  }

                  function showTooltip(x, y, contents) {
                      $("<div id='tooltip'>" + contents + "</div>").css( {
                          position: "absolute",
                          display: "none",
                          top: y + 5,
                          left: x + 5,
                          border: "1px solid #fdd",
                          padding: "2px",
                          "background-color": "#fee",
                          opacity: 0.80
                      }).appendTo("body").fadeIn(200);
                  }

                  function zoomClick() {
                      $("#zoom").hide();
                      drawIt();
                  };

                  var previousPoint = null;

                  $("#thegraph").bind("plothover", function (event, pos, item) {
                      $("#x").text(pos.x.toFixed(2));
                      $("#y").text(pos.y.toFixed(2));
                      if (item) {
                          if (previousPoint != item.datapoint) {
                              previousPoint = item.datapoint;
                              $("#tooltip").remove();
                              var x = item.datapoint[0].toFixed(0), y = item.datapoint[1].toFixed(2);
                              var d = new Date(Number(x));
                              showTooltip(item.pageX, item.pageY, item.series.label + " = " + y + "<br />" + d.toString());
                          }
                      }
                  });
                {/literal}
              </script>
