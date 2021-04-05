import React from "react";
import Layout from "../../Shared/Layout";
import { InertiaLink, usePage } from "@inertiajs/inertia-react";
import { format } from "date-fns";
function Home() {
    const { events } = usePage().props;
    return (
        <>
            <h3>Upcoming events</h3>
            <hr />
            {events.map(
                ({
                    name,
                    slug,
                    startEvent,
                    endEvent,
                    description,
                    image_url,
                }) => (
                    <div key={slug}>
                        <div className="row event">
                            <h4 className="event-title text-primary">
                                <InertiaLink
                                    href={route("bookings.event.index", slug)}
                                >
                                    {name}
                                </InertiaLink>
                            </h4>
                            <p>
                                <i className="fas fa-calendar text-primary"></i>
                                {format(new Date(startEvent), "PP")}
                                <br />
                                <i className="fas fa-clock text-primary"></i>
                                {"  "}
                                {format(new Date(startEvent), "H:mm")}z -{" "}
                                {format(new Date(endEvent), "H:mm")}z
                            </p>
                            <div
                                dangerouslySetInnerHTML={{
                                    __html: description,
                                }}
                            ></div>
                            <div className="col-xl-6 col-lg-6 col-md-12 col-sm-12">
                                {image_url !== null && (
                                    <InertiaLink
                                        href={route(
                                            "bookings.event.index",
                                            slug
                                        )}
                                    >
                                        <img
                                            src="image_url"
                                            className="img-fluid rounded"
                                        ></img>
                                    </InertiaLink>
                                )}
                            </div>
                        </div>
                        <hr />
                    </div>
                )
            )}
            {events.length === 0 && <p>Currently no events scheduled.</p>}
        </>
    );
}

Home.layout = (page) => <Layout children={page} title="Home" />;

export default Home;
