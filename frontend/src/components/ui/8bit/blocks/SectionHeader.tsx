import React from 'react';

interface Props {
    action?: React.ReactNode;
}

export function SectionHeader({ action }: Props) {
    return (
    <div className="flex items-center justify-between mb-8 border-b-2 border-dashed border-[#004400] pb-4">
        <div>
            <p className="text-xs mt-1 text-green-500">{">"} ACCESSING_RECORDS... OK</p>
        </div>
        {action && (
            <div>
                {action}
            </div>
        )}
    </div>
    );
};
