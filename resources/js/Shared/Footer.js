import React from "react";
import { usePage } from "@inertiajs/inertia-react";

function Footer() {
    const { app } = usePage().props;
    return (
        <footer>
            2018
            {new Date().getFullYear() > 2018 && "-" + new Date().getFullYear()}{" "}
            Booking System by Dave Roverts (1186831). Used and maintained by{" "}
            <a href={app.division_url} target="_blank">
                {app.division}
            </a>
        </footer>
    );
}

export default Footer;
