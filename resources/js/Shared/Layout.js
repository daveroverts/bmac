import Helmet from "react-helmet";
import { usePage } from "@inertiajs/inertia-react";
import { Container } from "reactstrap";
import Navbar from "@/Shared/Navbar";
import Footer from "@/Shared/Footer";

export default function Layout({ title, children }) {
    const { app } = usePage().props;
    return (
        <div>
            <Helmet titleTemplate={`%s | ${app.name}`} title={title} />
            <Navbar />
            <main className="py-4">
                <Container>
                    {/* Breadcrumbs */}
                    {children}
                    <Footer />
                </Container>
            </main>
        </div>
    );
}
