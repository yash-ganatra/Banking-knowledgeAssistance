import { motion, useMotionValue, useTransform, useSpring } from "framer-motion";
import { useEffect } from "react";

export function RotatingCube({ size = 288, layoutId, className, textColor = "text-white" }) {
    // Half size (translateZ) is size / 2
    const halfSize = `${size / 2}px`;

    const x = useMotionValue(0);
    const y = useMotionValue(0);

    // Smooth spring animation
    const springConfig = { damping: 20, stiffness: 100 };
    const rotateX = useSpring(useTransform(y, [0, window.innerHeight], [45, -45]), springConfig);
    const rotateY = useSpring(useTransform(x, [0, window.innerWidth], [-45, 45]), springConfig);

    useEffect(() => {
        function handleMouse(e) {
            x.set(e.clientX);
            y.set(e.clientY);
        }

        window.addEventListener("mousemove", handleMouse);
        return () => window.removeEventListener("mousemove", handleMouse);
    }, [x, y]);

    // Adjust font sizes based on cube size
    const isSmall = size < 100;
    const fontSizeLg = isSmall ? "text-[0px]" : "text-2xl";
    const fontSizeMd = isSmall ? "text-[0px]" : "text-xl";
    const fontSizeSm = isSmall ? "text-[0px]" : "text-lg";

    // Dynamic classes based on text color context
    // If text is not white (e.g. blue), we likely want a lighter face background
    const faceBgClass = textColor === "text-white"
        ? "bg-primary-500/30 backdrop-blur-sm border-white/20"
        : "bg-blue-100/30 backdrop-blur-sm border-blue-500/20";

    return (
        <motion.div
            layoutId={layoutId}
            className={`relative flex items-center justify-center perspective-1000 ${className}`}
            style={{ width: size, height: size }}
        >
            {/* Gradient glow/shadow behind the cube - only show when large */}
            {!isSmall && (
                <div className={`absolute w-96 h-96 rounded-full blur-3xl -z-10 ${textColor === "text-white" ? "bg-primary-500/20" : "bg-blue-500/10"}`} />
            )}

            <motion.div
                style={{
                    rotateX,
                    rotateY,
                    transformStyle: "preserve-3d",
                    width: "100%",
                    height: "100%"
                }}
                className="preserve-3d relative"
            >
                <motion.div
                    animate={{
                        rotateX: [0, 360],
                        rotateY: [0, 360],
                    }}
                    transition={{
                        duration: 20,
                        repeat: Infinity,
                        ease: "linear",
                    }}
                    className="w-full h-full preserve-3d relative"
                    style={{ transformStyle: "preserve-3d" }}
                >
                    {/* Faces common classes for Glass Effect */}

                    {/* Front - "Customer" */}
                    <div className={`absolute inset-0 ${faceBgClass} shadow-inner backface-hidden flex items-center justify-center ${textColor} font-semibold ${fontSizeLg} border`}
                        style={{ transform: `translateZ(${halfSize})` }}>
                        Customer
                    </div>

                    {/* Back - "Communication" */}
                    <div className={`absolute inset-0 ${faceBgClass} shadow-inner backface-hidden flex items-center justify-center ${textColor} font-semibold ${fontSizeMd} border`}
                        style={{ transform: `rotateY(180deg) translateZ(${halfSize})` }}>
                        Communication
                    </div>

                    {/* Right - "Compliance" */}
                    <div className={`absolute inset-0 ${faceBgClass} shadow-inner backface-hidden flex items-center justify-center ${textColor} font-semibold ${fontSizeLg} border`}
                        style={{ transform: `rotateY(90deg) translateZ(${halfSize})` }}>
                        Compliance
                    </div>

                    {/* Left - "Confidentiality" */}
                    <div className={`absolute inset-0 ${faceBgClass} shadow-inner backface-hidden flex items-center justify-center ${textColor} font-semibold ${fontSizeSm} border`}
                        style={{ transform: `rotateY(-90deg) translateZ(${halfSize})` }}>
                        Confidentiality
                    </div>

                    {/* Top - "Credibility" */}
                    <div className={`absolute inset-0 ${faceBgClass} shadow-inner backface-hidden flex items-center justify-center ${textColor} font-semibold ${fontSizeLg} border`}
                        style={{ transform: `rotateX(90deg) translateZ(${halfSize})` }}>
                        Credibility
                    </div>

                    {/* Bottom - "Convenience" */}
                    <div className={`absolute inset-0 ${faceBgClass} shadow-inner backface-hidden flex items-center justify-center ${textColor} font-semibold ${fontSizeLg} border`}
                        style={{ transform: `rotateX(-90deg) translateZ(${halfSize})` }}>
                        Convenience
                    </div>
                </motion.div>
            </motion.div>
        </motion.div>
    );
}
