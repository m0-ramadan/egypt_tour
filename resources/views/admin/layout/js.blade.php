@include('admin.i18n.locale')
<!-- build:js dashboard/assets/vendor/js/core.js -->

<script src="{{ asset('dashboard/assets/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('dashboard/assets/vendor/libs/popper/popper.js') }}"></script>
<script src="{{ asset('dashboard/assets/vendor/js/bootstrap.js') }}"></script>
<script src="{{ asset('dashboard/assets/vendor/libs/node-waves/node-waves.js') }}"></script>
<script src="{{ asset('dashboard/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
<script src="{{ asset('dashboard/assets/vendor/libs/hammer/hammer.js') }}"></script>
<script src="{{ asset('dashboard/assets/vendor/libs/i18n/i18n.js') }}"></script>
<script src="{{ asset('dashboard/assets/vendor/libs/typeahead-js/typeahead.js') }}"></script>
<script src="{{ asset('dashboard/assets/vendor/js/menu.js') }}"></script>
<script src="{{ asset('dashboard/assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
<script src="{{ asset('dashboard/assets/vendor/libs/swiper/swiper.js') }}"></script>
<script src="{{ asset('dashboard/assets/js/main.js') }}"></script>
<script src="{{ asset('dashboard/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
<script src="{{ asset('dashboard/assets/js/dashboards-analytics.js') }}"></script>
<script src="{{ asset('dashboard/assets/vendor/libs/select2/select2.js') }}"></script>
<script src="{{ asset('dashboard/assets/vendor/libs/bootstrap-select/bootstrap-select.js') }}"></script>

<!-- endbuild -->


<!-- Page JS -->
<script src="{{ asset('dashboard/assets/js/app-ecommerce-order-list.js') }}"></script>
<script src="{{ asset('dashboard/assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
<script src="{{ asset('dashboard/assets/js/extended-ui-sweetalert2.js') }}"></script>


<div class="modal fade" id="exLargeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-content">
                <img class="img_modal">
                <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                        {{ admin_t('إلغاء') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script> --}}

<script src="{{ asset('dashboard/assets/vendor/libs/toastr/toastr.js') }}"></script>
<script src="{{ asset('dashboard/assets/js/app-logistics-dashboard.js') }}"></script>

<script>
    let labelColor, cardColor, headingColor, borderColor, legendColor;




    if (isDarkStyle) {
        cardColor = config.colors_dark.cardColor;

        labelColor = config.colors_dark.textMuted;
        headingColor = config.colors_dark.headingColor;
        borderColor = config.colors_dark.borderColor;
        legendColor = config.colors_dark.bodyColor;
    } else {
        cardColor = config.colors.cardColor;

        labelColor = config.colors.textMuted;
        headingColor = config.colors.headingColor;
        borderColor = config.colors.borderColor;
        legendColor = config.colors.bodyColor;
    }




    // Donut Chart Colors
    const chartColors = {
        // donut: {
        //     series1: '#22A95E',
        //     series2: '#24B364',
        //     series3: config.colors.success,
        //     series4: '#53D28C',
        //     series5: '#7EDDA9',
        //     series6: '#A9E9C5'
        // },

        column: {
            series1: '#826af9',
            series2: '#d2b0ff',
            bg: '#f8d3ff'
        },
        donut: {
            series1: '#fee802',
            series2: '#3fd0bd',
            series3: '#826bf8',
            series4: '#2b9bf4'
        },
        area: {
            series1: '#fee802',
            series2: '#60f2ca',
            series3: '#826bf8'
        }
    };







    const expensesRadialChartEl = document.querySelector('#expensesChart'),
        expensesRadialChartConfig = {
            chart: {
                height: 145,
                sparkline: {
                    enabled: true
                },
                parentHeightOffset: 0,
                type: 'radialBar'
            },
            colors: [config.colors.success],
            series: [0.00],
            plotOptions: {
                radialBar: {
                    offsetY: 0,
                    startAngle: -90,
                    endAngle: 90,
                    hollow: {
                        size: '65%'
                    },
                    track: {
                        strokeWidth: '45%',
                        background: borderColor
                    },
                    dataLabels: {
                        name: {
                            show: false
                        },
                        value: {
                            fontSize: '22px',
                            color: headingColor,
                            fontWeight: 500,
                            offsetY: -5
                        }
                    }
                }
            },
            grid: {
                show: false,
                padding: {
                    bottom: 5
                }
            },
            stroke: {
                lineCap: 'round'
            },
            labels: ['Progress'],
            responsive: [{
                    breakpoint: 1442,
                    options: {
                        chart: {
                            height: 120
                        },
                        plotOptions: {
                            radialBar: {
                                dataLabels: {
                                    value: {
                                        fontSize: '18px'
                                    }
                                },
                                hollow: {
                                    size: '60%'
                                }
                            }
                        }
                    }
                },
                {
                    breakpoint: 1025,
                    options: {
                        chart: {
                            height: 136
                        },
                        plotOptions: {
                            radialBar: {
                                hollow: {
                                    size: '65%'
                                },
                                dataLabels: {
                                    value: {
                                        fontSize: '18px'
                                    }
                                }
                            }
                        }
                    }
                },
                {
                    breakpoint: 769,
                    options: {
                        chart: {
                            height: 120
                        },
                        plotOptions: {
                            radialBar: {
                                hollow: {
                                    size: '55%'
                                }
                            }
                        }
                    }
                },
                {
                    breakpoint: 426,
                    options: {
                        chart: {
                            height: 145
                        },
                        plotOptions: {
                            radialBar: {
                                hollow: {
                                    size: '65%'
                                }
                            }
                        },
                        dataLabels: {
                            value: {
                                offsetY: 0
                            }
                        }
                    }
                },
                {
                    breakpoint: 376,
                    options: {
                        chart: {
                            height: 105
                        },
                        plotOptions: {
                            radialBar: {
                                hollow: {
                                    size: '60%'
                                }
                            }
                        }
                    }
                }
            ]
        };
    if (typeof expensesRadialChartEl !== undefined && expensesRadialChartEl !== null) {
        const expensesRadialChart = new ApexCharts(expensesRadialChartEl, expensesRadialChartConfig);
        expensesRadialChart.render();
    }



    const areaChartEl = document.querySelector('#lineAreaChart1'),
        areaChartConfig = {
            chart: {
                height: 400,
                type: 'area',
                parentHeightOffset: 0,
                toolbar: {
                    show: false
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: false,
                curve: 'straight'
            },
            legend: {
                show: true,
                position: 'top',
                horizontalAlign: 'start',
                labels: {
                    colors: legendColor,
                    useSeriesColors: false
                }
            },
            grid: {
                borderColor: borderColor,
                xaxis: {
                    lines: {
                        show: true
                    }
                }
            },
            colors: [chartColors.area.series3, chartColors.area.series2, chartColors.area.series1],
            series: [{
                    name: "العملاء",
                    data: "4,26,16,492,127,9,1,0,0,0,0".split(",")
                },
                {
                    name: "السائقين",
                    data: "0,0,0,775,143,12,2,1,0,0,0".split(",")
                },
                {
                    name: "الرحلات",
                    data: "0,0,0,63,0,0,0,0,0,0,0".split(",")
                }
            ],
            xaxis: {
                categories: "1-2025,2-2025,3-2025,4-2025,5-2025,6-2025,7-2025,8-2025,9-2025,10-2025,11-2025,1-2025,2-2025,3-2025,4-2025,5-2025,6-2025,7-2025,8-2025,9-2025,10-2025,11-2025"
                    .split(","),
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                },
                labels: {
                    style: {
                        colors: labelColor,
                        fontSize: '13px'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: labelColor,
                        fontSize: '13px'
                    }
                }
            },
            fill: {
                opacity: 1,
                type: 'solid'
            },
            tooltip: {
                shared: false
            }
        };
    if (typeof areaChartEl !== undefined && areaChartEl !== null) {
        const areaChart = new ApexCharts(areaChartEl, areaChartConfig);
        areaChart.render();
    }


    const areaChartE2 = document.querySelector('#lineAreaChart'),
        areaChartConfig1 = {
            chart: {
                height: 400,
                type: 'area',
                parentHeightOffset: 0,
                toolbar: {
                    show: false
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: false,
                curve: 'straight'
            },
            legend: {
                show: true,
                position: 'top',
                horizontalAlign: 'start',
                labels: {
                    colors: legendColor,
                    useSeriesColors: false
                }
            },
            grid: {
                borderColor: borderColor,
                xaxis: {
                    lines: {
                        show: true
                    }
                }
            },
            colors: [chartColors.area.series3, chartColors.area.series2],
            series: [{
                name: "الرحلات المكتملة",
                data: "0,0,0,11,0,0,0,0,0,0,0".split(",")
            }, {
                name: "الرحلات الملغاة",
                data: "0,0,0,51,0,0,0,0,0,0,0".split(",")
            }, ],
            xaxis: {
                categories: "1-2025,2-2025,3-2025,4-2025,5-2025,6-2025,7-2025,8-2025,9-2025,10-2025,11-2025,1-2025,2-2025,3-2025,4-2025,5-2025,6-2025,7-2025,8-2025,9-2025,10-2025,11-2025"
                    .split(","),
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                },
                labels: {
                    style: {
                        colors: labelColor,
                        fontSize: '13px'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: labelColor,
                        fontSize: '13px'
                    }
                }
            },
            fill: {
                opacity: 1,
                type: 'solid'
            },
            tooltip: {
                shared: false
            }
        };
    if (typeof areaChartE2 !== undefined && areaChartE2 !== null) {
        const areaChart = new ApexCharts(areaChartE2, areaChartConfig1);
        areaChart.render();
    }











    // month


    // Profit last month Line Chart
    // --------------------------------------------------------------------
    // const profitLastMonthEl = document.querySelector('#profitLastMonth'),
    //     profitLastMonthConfig = {
    //         chart: {
    //             height: 90,
    //             type: 'line',
    //             parentHeightOffset: 0,
    //             toolbar: {
    //                 show: false
    //             }
    //         },
    //         grid: {
    //             borderColor: borderColor,
    //             strokeDashArray: 6,
    //             xaxis: {
    //                 lines: {
    //                     show: true,
    //                     colors: '#000'
    //                 }
    //             },
    //             yaxis: {
    //                 lines: {
    //                     show: false
    //                 }
    //             },
    //             padding: {
    //                 top: -18,
    //                 left: -4,
    //                 right: 7,
    //                 bottom: -10
    //             }
    //         },
    //         colors: [config.colors.danger],
    //         stroke: {
    //             width: 2
    //         },
    //         series: [{
    //             data: [0, 0, 10, 50]
    //         }],
    //         tooltip: {
    //             shared: false,
    //             intersect: true,
    //             x: {
    //                 show: false
    //             }
    //         },
    //         xaxis: {
    //             labels: {
    //                 show: false
    //             },
    //             axisTicks: {
    //                 show: false
    //             },
    //             axisBorder: {
    //                 show: false
    //             }
    //         },
    //         yaxis: {
    //             labels: {
    //                 show: false
    //             }
    //         },
    //         tooltip: {
    //             enabled: false
    //         },
    //         markers: {
    //             size: 3.5,
    //             fillColor: config.colors.info,
    //             strokeColors: 'transparent',
    //             strokeWidth: 3.2,
    //             discrete: [{
    //                 seriesIndex: 0,
    //                 dataPointIndex: 5,
    //                 fillColor: cardColor,
    //                 strokeColor: config.colors.info,
    //                 size: 5,
    //                 shape: 'circle'
    //             }],
    //             hover: {
    //                 size: 5.5
    //             }
    //         },
    //         responsive: [{
    //                 breakpoint: 1442,
    //                 options: {
    //                     chart: {
    //                         height: 100
    //                     }
    //                 }
    //             },
    //             {
    //                 breakpoint: 1025,
    //                 options: {
    //                     chart: {
    //                         height: 86
    //                     }
    //                 }
    //             },
    //             {
    //                 breakpoint: 769,
    //                 options: {
    //                     chart: {
    //                         height: 93
    //                     }
    //                 }
    //             }
    //         ]
    //     };
    // if (typeof profitLastMonthEl !== undefined && profitLastMonthEl !== null) {
    //     const profitLastMonth = new ApexCharts(profitLastMonthEl, profitLastMonthConfig);
    //     profitLastMonth.render();
    // }
</script>
<script>
    $(function() {
        $('.select2').select2();

        if ("") {
            console.log("")
            $('#sales_data').html(`احصائات الرحلات 2025`);
        }
        if ("") {
            console.log("")

            $('#all_data').html(`الاحصائيات لسنة 2025`);
        }



        $('#year').on('change', function() {
            var selectedYear = $(this).val();
            if (selectedYear) {
                // Reload page with the selected year as a query parameter
                window.location.href = '?sales_year=' + selectedYear;

            }
        });
        $('#data_year').on('change', function() {
            var selectedYear = $(this).val();
            if (selectedYear) {
                // Reload page with the selected year as a query parameter
                window.location.href = '?data_year=' + selectedYear;
            }
        });
    })
</script>


<script>
    // Line Area Chart
    // --------------------------------------------------------------------
</script>

<script>
    $(function() {

        $(".img_popup").click(function() {
            console.log("test")
            var src_img = $(this).attr("src")
            $(".img_modal").attr("src", src_img)
            $("#exLargeModal").modal('show')
        })


        $(".btn-action").click(function() {
            var url = $(this).data("url");
            var method = $(this).data("method");
            var message = $(this).data("message");
            var text_btn_confirm = $(this).data("text_btn_confirm");
            var text_btn_cancel = $(this).data("text_btn_cancel");


            console.log(url, method)

            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: "btn btn-danger",
                    cancelButton: "btn btn-primary"
                },
                buttonsStyling: false
            });
            swalWithBootstrapButtons.fire({
                text: message,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: text_btn_confirm,
                cancelButtonText: text_btn_cancel,
                reverseButtons: true
            }).then((result) => {

                if (result.isConfirmed) {
                    $("#form_action_" + method).attr("action", url).submit();
                    console.log("Done")
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    console.log("else Done")

                }
            });
        })





        // Pusher.logToConsole = true;

        // var pusher = new Pusher("0a0f927bacf0d6a676c5", {
        //     cluster: 'eu'
        // });

        // var channel = pusher.subscribe('channel-notify-1');


        $("#view-all-notifications").click(function(e) {
            e.preventDefault()
            var _this_button = $(this);
            var next_url = $(this).attr("href");


            // You can get url_string from window.location.href if you want to work with
            // the URL of the current page


            // if (next_url) {
            //     var url = new URL(next_url);
            //     var page = url.searchParams.get("page");



            //     $.ajax({
            //         url: "https://seda.codeella.com/admin/notifications?page=" + page,
            //         success: function(res) {
            //             console.log(res)
            //             $.each(res.data.data, function(key, val) {

            //                 // console.log(val);
            //                 $("#list_notifications").append(`
            //                         <li style="cursor :unset"  class="list-group item list-group-item-action dropdown-notifications-item">
            //                             <div style="cursor :unset" class="d-flex">

            //                                 <div class="flex-grow-1">
            //                                         <h6 class="mb-1">${val.title}</h6>
            //                                     <p class="mb-0">${val.message}</p>
            //                                     <small class="text-muted text-nowrap">${val.created_at}</small>
            //                                 </div>
            //                             </div>
            //                         </li>
            //                     `);
            //             })


            //             if (!res.data.links.next) {
            //                 $("#view-all-notifications").remove();

            //             } else {

            //                 document.querySelector('.notification-body').scrollTo(0,
            //                     document.querySelector(
            //                         '.notification-body').scrollHeight);
            //                 $("#view-all-notifications").attr("href", res.data.links.next);

            //             }


            //         }
            //     })



            // }



        })





        // channel.bind('channel-notify', function(data) {


        //     toastr.options = {
        //         "closeButton": false,
        //         "debug": false,
        //         "newestOnTop": false,
        //         "progressBar": false,
        //         "positionClass": "toast-top-right",
        //         "preventDuplicates": false,
        //         "onclick": null,
        //         "showDuration": "300",
        //         "hideDuration": "1000",
        //         "timeOut": "5000",
        //         "extendedTimeOut": "1000",
        //         "showEasing": "swing",
        //         "hideEasing": "linear",
        //         "showMethod": "fadeIn",
        //         "hideMethod": "fadeOut"
        //     }

        //     // Original ISO format date string
        //     let isoDateString = data.data.created_at;

        //     // Create a new Date object from the ISO string
        //     let date = new Date(isoDateString);

        //     // Get the components
        //     let hours = date.getHours();
        //     let minutes = ('0' + date.getMinutes()).slice(-2);
        //     let seconds = ('0' + date.getSeconds()).slice(-2);

        //     // Convert to 12-hour format (no AM/PM)
        //     hours = hours % 12;
        //     hours = hours ? hours : 12; // The hour '0' should be '12'

        //     // Format the date to 'YYYY-MM-DD HH:MM:SS'
        //     let formattedDate = date.getFullYear() + '-' +
        //         ('0' + (date.getMonth() + 1)).slice(-2) + '-' +
        //         ('0' + date.getDate()).slice(-2) + ' ' +
        //         ('0' + hours).slice(-2) + ':' +
        //         minutes + ':' +
        //         seconds;

        //     $("#list_notifications").prepend(`
        //             <li style="cursor :unset"  class="list-group item list-group-item-action dropdown-notifications-item">
        //             <div style="cursor :unset" class="d-flex">
        //             <div class="flex-shrink-0 me-3">

        //             </div>
        //             <div class="flex-grow-1">
        //             <h6 class="mb-1">${data.data.title}</h6>
        //             <p class="mb-0">${data.data.message}</p>
        //             <small class="text-muted text-nowrap">${formattedDate}</small>
        //             </div>
        //             </div>
        //             </li>
        //             `);
        //     toastr["info"](data.data.message)
        //     console.log(data.data)
        //     var audio = new Audio("https://seda.codeella.com/sound_notify.mp3");
        //     audio.play();
        // });



    })
</script>
