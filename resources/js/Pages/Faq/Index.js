import Layout from "@/Shared/Layout";
import { usePage } from "@inertiajs/inertia-react";
function Faq() {
    const { faq, events } = usePage().props;
    return (
        <>
            {events.map(({ id, name, faqs }) => (
                <div key={id}>
                    <h3>FAQ for {name}</h3>
                    {faqs.map(({ id, question, answer }) => (
                        <p key={id}>
                                <strong>{question}</strong>
                                <br />
                                <div
                                    dangerouslySetInnerHTML={{
                                        __html: answer,
                                    }}
                                />
                        </p>
                    ))}
                </div>
            ))}
            <h3>General FAQ</h3>
            <hr />
            {faq.map(({ id, question, answer }) => (
                <p key={id}>
                        <strong>{question}</strong>
                        <br />
                        <div
                            dangerouslySetInnerHTML={{
                                __html: answer,
                            }}
                        />
                </p>
            ))}
            {faq.length === 0 && (
                <p>No Questions / Answers are available at the moment</p>
            )}
        </>
    );
}

Faq.layout = (page) => <Layout children={page} title="Faq" />;

export default Faq;
