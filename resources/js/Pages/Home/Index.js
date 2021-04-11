import React from "react";
import Layout from "../../Shared/Layout";
import { InertiaLink, usePage } from "@inertiajs/inertia-react";
import { format } from "date-fns";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faCalendar, faClock } from "@fortawesome/free-solid-svg-icons";
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
                            <div className="col-xl-6 col-lg-6 col-md-12 col-sm-12">
                                <h4 className="event-title text-primary">
                                    <InertiaLink
                                        href={route(
                                            "bookings.event.index",
                                            slug
                                        )}
                                    >
                                        {name}
                                    </InertiaLink>
                                </h4>
                                <p>
                                    <FontAwesomeIcon icon={faCalendar} />
                                    {"  "}
                                    {format(new Date(startEvent), "PP")}
                                    <br />
                                    <FontAwesomeIcon icon={faClock} />
                                    {"  "}
                                    {format(new Date(startEvent), "H:mm")}z -{" "}
                                    {format(new Date(endEvent), "H:mm")}z
                                </p>
                                <div
                                    dangerouslySetInnerHTML={{
                                        __html: description,
                                    }}
                                />
                                <InertiaLink
                                    href={route("bookings.event.index", slug)}
                                    className="btn btn-success"
                                >See Available Slots</InertiaLink>
                            </div>
                            <div className="col-xl-6 col-lg-6 col-md-12 col-sm-12">
                                {image_url !== null && (
                                    <InertiaLink
                                        href={route(
                                            "bookings.event.index",
                                            slug
                                        )}
                                    >
                                        <img
                                            src={image_url}
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
