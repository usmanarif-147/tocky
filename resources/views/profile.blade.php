<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>Tocky</title>

    <!-- Link Stylesheet -->
    <link rel="stylesheet" href="{{ asset('profile/style.css') }}" />
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous" />
    <!------------------------------------------------ Boxicon CDN ------------------------------------------->

    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
</head>

<body>
    <!-- Hero Section -->

    <section>
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-5 col-12 shadow-lg">
                    <!-- Profile Image Section -->

                    <div class="header_section shadow bg-white m-4 rounded-5">
                        <div class="py-3">
                            {{-- <div class="text-center">
                                <img src="{{ asset(isImageExist($user->cover_photo)) }}" class="img-fluid" alt="" />
                            </div> --}}
                            <div class="cover_image p-4">
                                <img src="{{ asset(isImageExist($user->cover_photo)) }}" class="img-fluid rounded-5"
                                    alt="" />
                            </div>
                            <div class="tikl_profile_image d-flex justify-content-center">
                                <div class="tikl_profile">
                                    <img src="{{ asset(isImageExist($user->photo)) }}" class="img-fluid"
                                        alt="" />
                                </div>
                            </div>
                            <div class="tikl_profile_content text-center">
                                <h1>{{ $user->name }}</h1>
                                <p>{{ $user->job_title }}, {{ $user->company }}</p>
                            </div>
                        </div>
                    </div>


                    <div class="info_section">
                        <div class="row d-flex justify-content-center">
                            <div class="col-11">
                                <div class="row mx-auto mt-3">
                                    <div class="p-1">
                                        <div class="change_button text-center">Save Contact</div>
                                    </div>
                                </div>

                                <!-- About Section -->

                                <div class="about_section mt-5">
                                    <h1>About</h1>
                                    <p>
                                        Creator of VittorTech and professional geek. Fulfilling a
                                        passion of tech and helping brand awareness along the way.
                                    </p>
                                    <p>
                                        Get in touch via email or social for collaborations or
                                        partnerships!
                                    </p>
                                </div>

                                <!-- Social Icons -->

                                <h5 class="py-3 headings">Social networks</h5>
                                @for ($i = 0; $i < count($userPlatforms); $i++)
                                    <div class="row {{ $i > 0 ? 'my-3' : '' }}">
                                        <div class="social-media-links d-flex gap-md-4 gap-1">
                                            @for ($j = 0; $j < count($userPlatforms[$i]); $j++)
                                                <div class="col-3">
                                                    <a href="#" class="social">
                                                        <img src="{{ asset(isImageExist($userPlatforms[$i][$j]->icon)) }}"
                                                            class="img-fluid" />
                                                    </a>
                                                </div>
                                            @endfor
                                        </div>
                                    </div>
                                @endfor


                                <!------------------------------------ Social Networks Section Ended --------------------------->

                                <!--------------------------------------- Video Section Started -------------------------------->

                                {{-- <div class="">
                                    <h5 class="headings py-3">Video</h5>
                                    <div class="video">
                                        <iframe width="100%" height="250"
                                            src="https://www.youtube.com/embed/7AMaw_XecD0"
                                            title="Ultimate Smart Business Card Comparison - OVOU, Linq, Popl, NOMAD, Tappy, Blue, Dot, V1CE"
                                            frameborder="0"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                            allowfullscreen></iframe>
                                        <p class="text-secondary pt-3">
                                            Ultimate Smart Business Card Comparison (Optional)
                                        </p>
                                    </div>
                                    <div class="video">
                                        <iframe width="100%" height="250"
                                            src="https://www.youtube.com/embed/7AMaw_XecD0"
                                            title="Ultimate Smart Business Card Comparison - OVOU, Linq, Popl, NOMAD, Tappy, Blue, Dot, V1CE"
                                            frameborder="0"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                            allowfullscreen></iframe>
                                        <p class="text-secondary pt-3">
                                            Ultimate Smart Business Card Comparison (Optional)
                                        </p>
                                    </div>
                                </div> --}}

                                <!------------------------------------------- Video Section Ended ------------------------------>

                                <!----------------------------------------- Contact Section Started ---------------------------->

                                <div class="social-info">
                                    <h5 class="headings py-3">Contact Info.</h5>
                                    <div class="row my-3">
                                        <div class="col-2">
                                            <i class="bx bx-envelope fs-4"></i>
                                        </div>
                                        <div class="col-10">
                                            <a href="mailto:business@vittortech.com" target="_blank"
                                                class="d-flex justify-content-between contact-links text-decoration-none text-dark">
                                                <div class="contact-information">
                                                    {{ $user->email }}
                                                </div>
                                                <div>
                                                    <span>
                                                        <i class="bx bx-chevron-right"></i>
                                                    </span>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="row my-3">
                                        <div class="col-2">
                                            <i class="bx bx-credit-card fs-4"></i>
                                        </div>
                                        <div class="col-10">
                                            <a href="https://VittorTech.com" target="_blank"
                                                class="d-flex justify-content-between contact-links text-decoration-none text-dark">
                                                <div class="contact-information">VittorTech.com</div>
                                                <div>
                                                    <span>
                                                        <i class="bx bx-chevron-right"></i>
                                                    </span>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="row my-3">
                                        <div class="col-2">
                                            <i class="bx bx-map fs-4"></i>
                                        </div>
                                        <div class="col-10">
                                            <a href="https://maps.google.com/?q=  Toronto Ontario  Canada"
                                                target="_blank"
                                                class="d-flex justify-content-between text-decoration-none text-dark">
                                                <div class="contact-information">
                                                    {{ $user->address }}
                                                </div>
                                                <div>
                                                    <span>
                                                        <i class="bx bx-chevron-right"></i>
                                                    </span>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!------------------------------------------ Contact Section Ended ----------------------------->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bootstrap script -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous">
    </script>
</body>

</html>
